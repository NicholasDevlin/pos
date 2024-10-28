class HandsontableWrapper {
    constructor({ tableId, programName = null, title = $('.header-title').text() }, options = {}) {
        this.tableId = tableId;
        this.programName = programName;
        this.options = options;
        this.title = title;

        this.#createTableWrapper();
        this.#createTableContainer();
        this.#createTableStatusBar();
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
                <button class="btn btn-sm btn-light mt-2" style="color: #56677d;" id="export-button-${this.tableId}" type="button">
                    <i class="fa fa-table mr-1"></i> Export Excel
                </button>
            </div>
        `;

        document.getElementById(this.tableWrapper).innerHTML += statusBar;
    }

    attachExportButtonEvent(hot) {
        const wrapper = this;

        const columnDelimiter = '|~|';
        const rowDelimiter = '\r\n';

        $(`#export-button-${this.tableId}`).on('click', function() {
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
                    columnHeaders: true,
                    exportHiddenColumns: false,
                    exportHiddenRows: false,
                    rowDelimiter,
                    rowHeaders: false,
                })
                .trim()
                .split(rowDelimiter);

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

            const columnWidths = [];
            const wrapTextColumnNumbers = [];
            const rowsArrayFormatted = rowsArray.map((row, rowIndex) => {
                Object.keys(row).map((column) => {
                    const value = row[column] ?? '';

                    let currentMaxValue = value.length;
                    if (value.includes('\n')) {
                        wrapTextColumnNumbers.push(parseInt(column) + 1);

                        const valueWithLineBreaks = value.split('\n');
                        currentMaxValue = Math.max(...valueWithLineBreaks.map((item) => item.length));
                    }

                    columnWidths[column] = Math.max(columnWidths[column] ?? 0, currentMaxValue + 4);
                })

                if (rowIndex === 0) {
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
                    return row.map((value) => {
                        return {
                            v: value,
                            t: 's',
                            s: {
                                alignment: { horizontal: 'center', vertical: 'center' },
                                border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } },
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

            wrapper.setWrapTextForColumns(worksheet, [...new Set(wrapTextColumnNumbers)]);

            const ROW_1_INDEX = 0;
            const ROW_2_INDEX = 1;

            /* create !rows array if it does not exist */
            if (!worksheet["!rows"]) worksheet["!rows"] = [];

            /* create row metadata object if it does not exist */
            if (!worksheet["!rows"][ROW_1_INDEX]) worksheet["!rows"][ROW_1_INDEX] = { hpx: 37.5 };
            if (!worksheet["!rows"][ROW_2_INDEX]) worksheet["!rows"][ROW_2_INDEX] = { hpx: 22.5 };

            XLSX.utils.book_append_sheet(workbook, worksheet, wrapper.title);

            XLSX.writeFile(workbook, `${wrapper.title}${wrapper.programName !== null ? ' [' + wrapper.programName + ']' : ''} - ${new Date(Date.now() + (7 * 60 * 60 * 1000)).toISOString().replace(/[-T:Z.]/g, '').slice(0, 14)}.xlsx`, { compression: true });

            if (hot.colToProp(0) === 'actions') {
                hidePlugin.showColumn(0);
            }
        });
    }

    checkIsContainsHTMLTag(str) {
        return /<[a-z][\s\S]*>/i.test(str);
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

    build() {
        const wrapper = this;

        const hot = new Handsontable(document.getElementById(this.tableContainer), {
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

        this.attachExportButtonEvent(hot);

        return hot;
    }
}

window.HandsontableWrapper = HandsontableWrapper;
