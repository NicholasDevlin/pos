class HandsontableWrapper {
    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.options = options;

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
        const statusBar = document.createElement('div');
        statusBar.id = this.tableStatusBar;
        statusBar.style.cssText = `
            font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Helvetica Neue,Arial,sans-serif;
            font-size: 13px;
            margin-top: 0.75em;
        `;
        statusBar.innerHTML = `Showing <span class="filtered"></span> of <span class="total"></span> entries.`;

        document.getElementById(this.tableWrapper).appendChild(statusBar);
    }

    build() {
        const wrapper = this;

        return new Handsontable(document.getElementById(this.tableContainer), {
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
            ...this.options,
        });
    }
}

window.HandsontableWrapper = HandsontableWrapper;
