class HandsontableWrapper {
    constructor({ tableId, programName = null, title = $('.header-title').text(), isExportEnabled = true }, options = {}) {
        this.tableId = tableId;
        this.programName = programName;
        this.options = options;
        this.title = title;
        this.isExportEnabled = isExportEnabled;
        this.hot = null;

        this.#createTableWrapper();
        this.#createTableContainer();
        this.#createTableStatusBar();

        // Return a Proxy object for dynamic method forwarding
        return new Proxy(this, {
            get: (target, prop) => {
                // Check if the property exists in the wrapper (HandsontableWrapper instance)
                if (prop in target) {
                    return target[prop];
                }

                // Check if the property exists in the Handsontable instance
                if (target.hot) {
                    const instanceProp = target.hot[prop];

                    // If it's a function, bind it to the Handsontable instance
                    if (typeof instanceProp === 'function') {
                        return instanceProp.bind(target.hot); // Bind the 'this' context for safer and future-proof way to forward to Handsontable instance
                    }

                    // Otherwise, return the property as is (e.g., non-function properties)
                    return instanceProp;
                }

                return undefined;
            },
        });
    }

    get tableWrapper() {
        return `${this.tableId}__wrapper`;
    }

    get tableContainer() {
        return `${this.tableId}__container`;
    }

    get tableStatusBar() {
        return `${this.tableId}__status-bar`;
    }

    #createTableWrapper() {
        document.getElementById(this.tableId).setAttribute('oncontextmenu', 'return false');
        document.getElementById(this.tableId).id = this.tableWrapper;
    }

    #createTableContainer() {
        const container = document.createElement('div');
        container.id = this.tableContainer;
        container.style.cssText = `
            background-color: rgb(240, 240, 240);
            overflow: scroll;
        `;

        document.getElementById(this.tableWrapper).appendChild(container);
    }

    #createTableStatusBar() {
        const statusBar = `
            <div style="display: flex; justify-content: space-between;">
                <div id="${this.tableStatusBar}" style="font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Helvetica Neue,Arial,sans-serif; font-size: 13px; margin-top: 0.75em;">
                    Showing <span class="filtered"></span> of <span class="total"></span> entries.
                </div>
                ${this.isExportEnabled ? `
                    <button class="btn btn-sm btn-light mt-2" style="color: #56677d;" id="export-button-${this.tableId}" type="button">
                        <i class="fa fa-table mr-1"></i> Export Excel
                    </button>
                ` : ''}
            </div>
        `;

        document.getElementById(this.tableWrapper).innerHTML += statusBar;
    }

    attachExportButtonEvent(hot) {
        const wrapper = this;

        const columnDelimiter = '|~|';
        const rowDelimiter = '\r\n';
        const nestedHeaders = wrapper.options.nestedHeaders
            ? this.trimEmptyBeginningOfNestedHeaderColumns(wrapper.options.nestedHeaders)
            : undefined;

        $(`#export-button-${this.tableId}`).off('click').on('click', function() {
            let hidePlugin;

            if (hot.colToProp(0) === 'actions') {
                hidePlugin = hot.getPlugin('hiddenColumns');
                hidePlugin.hideColumn(0);
            }

            const rowsString = hot
                .getPlugin('exportFile')
                .exportAsString('csv', {
                    bom: false,
                    columnDelimiter,
                    ...!nestedHeaders
                        ? { columnHeaders: true } : {},
                    exportHiddenColumns: false,
                    exportHiddenRows: false,
                    rowDelimiter,
                    rowHeaders: false,
                })
                .trim()
                .split(rowDelimiter);

            const headers = nestedHeaders?.map(row => {
                return row.map(col => {
                    const label = typeof col === 'object' ? wrapper.stripHtmlTags(col.label || '') : wrapper.stripHtmlTags(col);

                    if (typeof col === 'object' && col.colspan > 1) {
                        return [label, ...Array(col.colspan - 1).fill('')];
                    } else {
                        return label;
                    }
                }).flat();
            }) ?? [];

            const rowsArray = rowsString.map((row) => {
                return row.trim().split(columnDelimiter).map((value) => {
                    if (wrapper.checkIsContainsHTMLTag(value)) {
                        const tempElement = document.createElement('div');

                        let separator = ', ';
                        if (value.includes('<br>')) {
                            separator = '\n';
                            value = value.replace(/<br>/g, '');
                        }

                        // Set the inner HTML of the temporary element
                        tempElement.innerHTML = value;

                        value = Array.from(tempElement.children).map((child) => {
                            return child.textContent || child.innerText;
                        }).join(separator);
                    }

                    return value.trim().replace(/"/g, '');
                });
            });

            rowsArray.unshift(...headers);

            const merges = [];
            let headerStartRow = 1;
            nestedHeaders?.forEach((row, rowIndex) => {
                let colIndex = 0; // Start column
                row.forEach(col => {
                    if (typeof col === 'object' && col.colspan > 1) {
                        merges.push({
                            s: { r: headerStartRow + rowIndex, c: colIndex }, // Start cell
                            e: { r: headerStartRow + rowIndex, c: colIndex + col.colspan - 1 }, // End cell
                        });
                        colIndex += col.colspan;
                    } else {
                        colIndex++;
                    }
                });
            });

            const columnWidths = [];
            const wrapTextColumnNumbers = [];

            const hiddenColumnIndexes = new Set(wrapper.hot.getPlugin('hiddenColumns').getHiddenColumns());
            const visibleColumns = wrapper.options.columns.filter((_, index) => !hiddenColumnIndexes.has(index));
            const numericColIndexes = visibleColumns.reduce((acc, column, index) => {
                if (column.type === 'numeric') acc.push(index);
                return acc;
            }, []);

            const rowsArrayFormatted = rowsArray.map((row, rowIndex) => {
                if (!nestedHeaders || (rowIndex >= nestedHeaders.length - 1)) {
                    Object.keys(row).map((column) => {
                        const value = row[column] ?? '';

                        let currentMaxValue = value.length;
                        if (value.includes('\n')) {
                            wrapTextColumnNumbers.push(parseInt(column) + 1);

                            const valueWithLineBreaks = value.split('\n');
                            currentMaxValue = Math.max(...valueWithLineBreaks.map((item) => item.length));
                        }

                        columnWidths[column] = Math.max(columnWidths[column] ?? 0, currentMaxValue + 4);
                    });
                }

                if (nestedHeaders ? (rowIndex < nestedHeaders.length) : rowIndex === 0) {
                    return row.map((value) => {
                        return {
                            v: value,
                            t: 's',
                            s: {
                                font: { bold: true },
                                fill: { bgColor: { rgb: 'FFFF00' }, fgColor: { rgb: 'FFFF00' } },
                                alignment: { horizontal: 'center', vertical: 'center' },
                                border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } },
                            },
                        };
                    });
                } else {
                    return row.map((value, colIndex) => {
                        const isNumberType = numericColIndexes.includes(colIndex);
                        let isFloat = false;

                        if (isNumberType) {
                            const valueFrmt = Number(value);
                            if (!Number.isInteger(valueFrmt)) {
                                value = valueFrmt.toFixed(2);
                                isFloat = true;
                            }
                        }

                        return {
                            v: value,
                            t: isNumberType ? 'n' : 's',
                            s: {
                                alignment: { horizontal: 'center', vertical: 'center' },
                                border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } },
                                ...isNumberType ? { numFmt: `#,##0${isFloat ? '.00' : ''}` } : {},
                            },
                        };
                    });
                }
            });

            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.aoa_to_sheet([
                [{
                    v: `  ${wrapper.title}`,
                    t: 's',
                    s: {
                        font: { sz: 18, bold: true },
                        alignment: { vertical: 'center' },
                    },
                }],
                ...rowsArrayFormatted,
            ]);
            worksheet["!cols"] = columnWidths.map((width) => ({ width }));
            worksheet['!merges'] = merges;

            wrapper.setWrapTextForColumns(worksheet, [...new Set(wrapTextColumnNumbers)]);

            const ROW_1_INDEX = 0;
            const ROW_2_INDEX = 1;

            /* create !rows array if it does not exist */
            if (!worksheet["!rows"]) worksheet["!rows"] = [];

            /* create row metadata object if it does not exist */
            if (!worksheet["!rows"][ROW_1_INDEX]) worksheet["!rows"][ROW_1_INDEX] = { hpx: 37.5 }; // Title
            if (nestedHeaders) {
                nestedHeaders.forEach((headerLevelRow, headerLevelIndex) => {
                    const headerIndex = headerLevelIndex + ROW_1_INDEX + 1;

                    if (!worksheet["!rows"][headerIndex]) worksheet["!rows"][headerIndex] = { hpx: 22.5 }; // Headers
                });
            } else {
                if (!worksheet["!rows"][ROW_2_INDEX]) worksheet["!rows"][ROW_2_INDEX] = { hpx: 22.5 }; // Headers
            }

            XLSX.utils.book_append_sheet(workbook, worksheet, wrapper.truncateWithEllipsis(wrapper.title));

            XLSX.writeFile(workbook, `${wrapper.title}${wrapper.programName !== null ? ' [' + wrapper.programName + ']' : ''} - ${new Date(Date.now() + (7 * 60 * 60 * 1000)).toISOString().replace(/[-T:Z.]/g, '').slice(0, 14)}.xlsx`, { compression: true });

            if (hot.colToProp(0) === 'actions') {
                hidePlugin.showColumn(0);
            }
        });
    }

    checkIsContainsHTMLTag(str) {
        return /<[a-z][\s\S]*>/i.test(str);
    }

    stripHtmlTags(input) {
        const div = document.createElement('div');
        div.innerHTML = input;
        return div.textContent || div.innerText || '';
    }

    setWrapTextForColumns(ws, columns) {
        const range = XLSX.utils.decode_range(ws['!ref']); // Get the worksheet range

        // Iterate through specified columns
        columns.forEach((col) => {
            const columnString = this.convertNumberToExcelColumn(col);

            for (let row = range.s.r + 2; row <= range.e.r; row++) { // Do not include header row
                const cellPosition = `${columnString}${row + 1}`;

                // Check if the cell exists
                if (ws[cellPosition]) {
                    if (!ws[cellPosition].s) {
                        ws[cellPosition].s = {}; // Ensure the style object exists
                    }
                    // Set wrapText to true
                    ws[cellPosition].s.alignment = {
                        ...ws[cellPosition].s.alignment,
                        wrapText: true,
                    };
                }
            }
        });
    }

    convertNumberToExcelColumn(num) {
        let column = '';
        let temp;

        while (num > 0) {
            temp = (num - 1) % 26;
            column = String.fromCharCode(temp + 65) + column; // 65 is the char code for 'A'
            num = Math.floor((num - temp) / 26);
        }

        return column;
    }

    reloadUjs() {
        $('.handsontable [data-remote]').off('click').on('click', function () {
            if ($.rails.allowAction($(this))) $.rails.handleRemote($(this));
            return false;
        });
    }

    updateSettings(options) {
        this.hot.updateSettings(options);
        this.options = { ...this.options, ...options };

        if (this.isExportEnabled) this.attachExportButtonEvent(this.hot);
    }

    trimEmptyBeginningOfNestedHeaderColumns(nestedHeaders) {
        let totalColumnsToRemove = 999;

        for (const row of nestedHeaders) {
            let columnToRemoveCount = 0;

            for (const cell of row) {
                if (typeof cell === 'object' && !cell.label) {
                    columnToRemoveCount += cell.colspan || 1; // Default colspan to 1 if not specified
                } else if (cell === '') {
                    columnToRemoveCount += 1;
                } else {
                    // Break the loop if a non-empty string or an object with label is found
                    break;
                }
            }

            totalColumnsToRemove = Math.min(totalColumnsToRemove, columnToRemoveCount); // Retain the smallest remove count across rows
        }

        return nestedHeaders.map(row => {
            let remainingToRemove = totalColumnsToRemove;

            return row.filter(cell => {
                if (remainingToRemove === 0) return true; // Keep cell if nothing to remove

                if (typeof cell === 'object' && !cell.label) {
                    // Decrease colspan and remove cell if it becomes zero
                    const decrement = Math.min(remainingToRemove, cell.colspan);
                    cell.colspan -= decrement;
                    remainingToRemove -= decrement;
                    return cell.colspan > 0;
                }

                if (cell === '') {
                    // Remove empty string
                    remainingToRemove--;
                    return false;
                }

                return true; // Keep cell if it's not removable
            });
        });
    }

    truncateWithEllipsis(str) {
        if (str.length > 31) {
            return str.slice(0, 28) + "...";
        }

        return str;
    }

    build() {
        const wrapper = this;

        this.hot = new Handsontable(document.getElementById(this.tableContainer), {
            className: 'htMiddle',
            height: '90vh',
            rowHeights: 35,
            licenseKey: 'non-commercial-and-evaluation',
            data: [],
            readOnly: true,
            filters: true,
            colHeaders: true,
            allowInsertColumn: false,
            allowRemoveColumn: false,
            allowInsertRow: false,
            allowRemoveRow: false,
            manualColumnFreeze: false,
            multiColumnSorting: true,
            hiddenColumns: {
                indicators: true,
            },
            contextMenu: ['hidden_columns_hide', 'hidden_columns_show'],
            dropdownMenu: [
                'filter_by_condition', 'filter_operators', 'filter_by_condition2',
                '---------',
                'filter_by_value', 'filter_action_bar',
            ],
            disableVisualSelection: ['area'],
            beforeOnCellMouseDown: function (e, coords, td) {
                if (td.className.includes('htDimmed')) {
                    e.stopImmediatePropagation();
                }

                if (td.className.includes('htActions')) {
                    e.preventDefault();
                }

                if (['A', 'BUTTON'].includes(e.targetTouches?.[0].target.tagName)) {
                    e.targetTouches?.[0].target.click();
                }
            },
            afterLoadData: function () {
                const totalRows = this.countRows();
                const statusBarId = wrapper.tableStatusBar;

                document.querySelector(`#${statusBarId} .filtered`).innerHTML = totalRows;
                document.querySelector(`#${statusBarId} .total`).innerHTML = totalRows;
            },
            afterFilter: function () {
                document.querySelector(`#${wrapper.tableStatusBar} .filtered`).innerHTML = this.countRows();
            },
            afterRender: () => this.reloadUjs(),
            afterScroll: () => this.reloadUjs(),
            ...this.options,
        });

        if (this.isExportEnabled) this.attachExportButtonEvent(this.hot);

        return wrapper;
    }
}

window.HandsontableWrapper = HandsontableWrapper;
