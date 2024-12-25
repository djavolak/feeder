export default class TableView {
    constructor(props)
    {
        this.crudTable = props.crudTable;
        this.baseCrudAction = this.crudTable.getAttribute('data-crud-action');
        this.createAction = `${this.baseCrudAction}form`;
        this.deleteAction = `${this.baseCrudAction}delete`;
        this.copyAsDraftAction = `${this.baseCrudAction}copyAsDraft`;
        this.bulkEditAction = `${this.baseCrudAction}bulkEditForm/`;
        this.bulkDeleteAction = `${this.baseCrudAction}bulkDelete/`;
        this.bulkSyncAction = `${this.baseCrudAction}bulkSync/`;
        this.crudTableBody = props.crudTableBody;
        this.action = `${this.baseCrudAction}tableHandler/`;
        this.isControlOpen = false;
        this.tableReadyEventName = 'tableReady';
        this.tableReadyEvent = new CustomEvent(this.tableReadyEventName);
        this.searchInput = document.getElementById('search') ?? null;
        this.delayOnInputSearchInMs = props.delayOnInputSearchInMs ?? 1000;
        this.addGhostRows = props.addGhostRows ?? false;
        this.searchOptions = document.querySelector('#searchOptions')
        this.selectPerPageListener();
        this.setPerPageValue();
        this.addSearchListener();
        this.addSearchOptionsEventListener();
        this.entityClass = props.entityClass;
        this.sortButtons = document.querySelectorAll('.sortColumn');
        this.writePermissions = true;
        this.draftable = false;
        this.useModal = false;
        this.filterContainer = document.getElementById('filterContainer');
        this.filterSelects = this.filterContainer ? this.filterContainer.querySelectorAll('select') : null;
        this.isFiltered = false;
        this.filteredCount = null;
        this.bulkEditable = false;
        this.initSort();
        this.addFilterListeners();
        this.filterData = props.filterData ? props.filterData : {};
        this.tablePopulatedEventName = 'tablePopulated';
        this.customFilterData = {};
    }

    setCustomFilterData(key, value, reRenderTable = false) {
        this.customFilterData[key] = value;
        if(reRenderTable) {
            this.setupTable(0, null);
        }
    }

    deleteCustomFilterData(key, reRenderTable = false) {
        delete this.customFilterData[key];
        if(reRenderTable) {
            this.setupTable(0, null);
        }
    }

    addFilterListeners() {
        if(this.filterSelects.length > 0) {
            this.filterSelects.forEach((select) => {
                select.addEventListener('change', () => {
                    this.setupTable(0, null);
                });
            });
        }
    }

    addSearchListener() {
        if(this.searchInput) {
            let timeout = null;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(timeout);
                if(this.searchInput.value.length === 0) {
                    this.setupTable(0, this.getActiveSort());
                    return;
                }
                if(this.searchInput.value.length > 3) {
                    timeout = setTimeout(() => {
                        this.setupTable(0, this.getActiveSort());
                    }, this.delayOnInputSearchInMs)
                }
            });
        }
    }
    addSearchOptionsEventListener() {
        if(this.searchInput && this.searchOptions) {
          this.searchOptions.addEventListener('change', () => {
              if(this.searchInput.value.length > 3) {
                  this.setupTable(0, this.getActiveSort());
              }
          })
        }
    }
    getFilterData() {
        let data = {};
        this.appendFiltersFromGet(data);
        if(this.filterSelects.length > 0) {
            this.filterSelects.forEach((select) => {
                if(select.value === '-1') {
                    return;
                }
                data[select.getAttribute('data-column')] = select.value;
            });
        }
        // merge with default filter
        let merged = Object.assign({}, data, this.filterData);
        return JSON.stringify(Object.assign({}, merged, this.getCustomFilterData()));
    }

    getCustomFilterData() {
        return this.customFilterData;
    }

    appendFiltersFromGet(data) {
        let searchParams = window.location.search;
        searchParams = searchParams.replace('?','');
        searchParams = searchParams.split('&');

        let tableHeaderNames = this.getTableHeaderNames().data;

        searchParams.forEach((param) => {
            let paramData = param.split('=');
            let columnExists = tableHeaderNames.some( headerName => headerName === paramData[0]);
            if(columnExists && paramData[1] !== '-1') {
                data[paramData[0]] = decodeURIComponent(paramData[1]);
            }
        });
        return data;
    }


    getData(page, sort)
    {
        this.perPage = localStorage.getItem('limitSelect') ?? document.getElementById('perPageSelector').value;
        let offset = 0;
        if (page > 1) {
            offset = (page - 1) * this.perPage;
        }
        console.log(`Sort is set to ${sort?.direction} by column ${sort?.column}`);
        let searchOption = this.searchOptions?.value;
        let newSearchValue =  this.searchInput.value;

        if (newSearchValue !== '') {
            if (searchOption === 'contains') {
                newSearchValue= '%' + this.searchInput.value + '%';
            }

            if (searchOption === 'startsWith') {
                newSearchValue= this.searchInput.value + '%';
            }
            if (searchOption === 'endsWith') {
                newSearchValue = '%' + this.searchInput.value ;
            }
        }

        let params = {
            limit: this.perPage,
            offset: offset,
            search: newSearchValue,
            searchOptions: this.searchCheckboxes,
            filter: this.getFilterData()
        }
        if(sort) {
            params.order = sort.direction;
            params.orderBy = sort.column;
        }
        return this.fetchResponse(params ,this.crudTableBody)
    }

    showBulkEditButtons() {
        document.querySelectorAll('.bulkApplyButton').forEach((button) => {
           button.classList.add('show');
        });
        document.querySelectorAll('.bulkAction').forEach((button) => {
            button.classList.add('show');
        });
        document.querySelectorAll('.selectAllRows').forEach((button) => {
            button.classList.add('show');
        });
    }

    dispatchTablePopulatedEvent() {
        this.crudTable.dispatchEvent(new CustomEvent(this.tablePopulatedEventName));
    }

    async populateTable(page = 0, sort = null)
    {
        let response = JSON.parse(await this.getData(page, sort));
        if(response.body.tableViewConfig.writePermissions === false) {
            this.writePermissions = false;
        }
        if(response.body.tableViewConfig.draftable === true) {
            this.draftable = true;
        }
        if(response.body.tableViewConfig.bulkEditable === true) {
            this.showBulkEditButtons();
        }
        if(response.body.tableViewConfig.useModal === true) {
            this.useModal = true;
        }
        if(response.body.filteredCount !== null) {
            this.isFiltered = true;
            this.filteredCount = response.body.filteredCount;
        } else {
            this.isFiltered = false;
            this.filteredCount = null;
        }
        this.totalCount = response.body.totalCount;
        this.resultsFound = response.body.resultsFound;
        let html = '';

        let missingNumberOfRows = this.perPage - response.body.data.length;

        if (response.success === true && response.body.data.length > 0) {
            let data = response.body.data;
            let tableHeaderNamesData = this.getTableHeaderNames();
            let tableHeaderNames = tableHeaderNamesData.data;
            for (let i = 0; i < data.length; i++) {
                html += `<tr data-id="${data[i].id}">`;
                tableHeaderNames.forEach((name, index) => {
                    if(index === tableHeaderNamesData.editColumnIndex) {
                        html += this.getActionElement(data[i]);
                    } else {
                        let value = data[i][name] ?? '';
                        if(typeof value === 'object') {
                            value = data[i][name].name;
                        }
                        html += this.generateTdByName(name,value);
                    }

                });
                html += `<td class="actionSection">
                <div class="actionContainer">
                    <div>
                        ${this.getActionElement(data[i], true)}
                    </div>`;
                if(this.draftable) {
                    html += `<div>
                        <div title="Copy as draft" class="copyAsDraft" data-id="${data[i].id}" data-action="${this.copyAsDraftAction}/${data[i].id}/">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                </svg>
                        </div>
                        </div>
                    `;
                }
                if(this.writePermissions) {
                    html += `<div>
                                    <div title="Delete" class="deleteEntity" data-id="${data[i].id}" 
                                                    data-action="${this.deleteAction}/${data[i].id}/">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    </div>
                                </div>`
                }
                html += `
                </div>
                </td>`;
                html += '</tr>';
            }
            if(this.addGhostRows) {
                for (let k = 0; k < missingNumberOfRows; k++) {
                    html += '<tr class="ghostRow">';
                    for (let l = 0; l < tableHeaderNames.length + 1; l++) {
                        html += '<td>'
                    }
                    html += '</tr>';
                }
            }
            this.crudTableBody.innerHTML = html;
            this.dispatchTablePopulatedEvent();
            return new Promise((resolve) => {
                document.body.dispatchEvent(this.tableReadyEvent);
                resolve(response);
            });
        } else {
            this.dispatchTablePopulatedEvent();
            this.crudTableBody.innerHTML = `<td class="tableMessage">${response.message ?? 'No results'}</td>`; //@todo send message through reseponse
        }
    }

    generateTdByName(name, value) {
        switch(name) {
            case 'status':
                return `<td class="status"><span class="${typeof value === 'string' ? value.toLowerCase() : value}">${value}</span></td>`
            default:
                return `<td>${value}</td>`
        }
    }

    getActionElement(data, isIcon = false) {
        if(this.useModal) {
            if(!isIcon) {
                return `<td><button class="editEntity btn btn-success" data-id="${data.id}"
                                data-action="${this.createAction}/${data.id}/">
                                ${data.name ?? data[`${this.crudTable.getAttribute('data-js-page').toLowerCase()}Name`]}
                            </button></td>`
            }
            return `<div title="Edit" class="editEntity" data-id="${data.id}"
                                        data-action="${this.createAction}/${data.id}/">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        </div>`;
        }
        if(!isIcon) {
            return `<td><a href="${this.createAction}/${data.id}/" title="${data.name ?? data[`${this.crudTable.getAttribute('data-js-page').toLowerCase()}Name`]}">${data.name ?? data[`${this.crudTable.getAttribute('data-js-page').toLowerCase()}Name`]}</a></td>`;
        }
        return `<a title="Edit" href="${this.createAction}/${data.id}/">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>                     
                        </a>`
    }

    getTableHeaderNames()
    {
        let tableHeaders = document.querySelectorAll('.tableHeader');
        let data = [];
        let editColumnIndex = 0;
        tableHeaders.forEach((elem,index) => {
            if(elem.getAttribute('data-editcolumn')) {
                editColumnIndex = index;
            }
            data.push(elem.getAttribute('data-columnname'));
        })
        return {data:data, editColumnIndex: editColumnIndex};
    }

    async fetchResponse(params, loaderContainer)
    {
        this.startLoader();
        const req = await fetch(this.action, {
            method: 'POST',
            body: new URLSearchParams(params)
        });
        this.stopLoader();
        return await req.text();
    }

    startLoader()
    {
        this.crudTableBody.classList.add('lowOpacity')
        if (!document.getElementById('loader')) {
            let loader = document.createElement('div');
            loader.id = 'loader';
            this.crudTable.appendChild(loader);
        }
    }

    stopLoader() {
        this.crudTableBody.classList.remove('lowOpacity');
        let loader = document.getElementById('loader');
        if(loader) {
            loader.remove();
        }
    }

    async generatePagination()
    {
        let maxNumberOfPages = Math.ceil(this.totalCount / this.perPage);
        if(this.filteredCount && this.isFiltered) {
            maxNumberOfPages = Math.ceil(this.filteredCount / this.perPage);
        }
        let html = '';
        if (maxNumberOfPages !== 1 && maxNumberOfPages !== 0) {
            html += `<button data-page="1" class="paginationButton btn btn-primary">First</button>`;
        }
        html += '<div class="paginationPagesWrapper">';
        html += '<div class="buttonContainer">'
        if (maxNumberOfPages !== 1 && maxNumberOfPages !== 0) {
            html += `<button class="prevButton btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg></button>`;
        }
        html += `<button data-page="1" class="activePagination btn btn-success">1</button>`;
        if (maxNumberOfPages !== 1 && maxNumberOfPages !== 0) {
            html += `<button class="nextButton btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg></button>`;
        }
        html += '</div>';
        html += '</div>';
        if (maxNumberOfPages !== 1 && maxNumberOfPages !== 0) {
            html += `<button data-page="${maxNumberOfPages}" class="paginationButton btn btn-primary">${maxNumberOfPages}</button>`;
        }
        html += '<div class="goToPage"><span>Go to page</span><input type="number" class="form-control goToPageInput"></div>';
        html += '<div class="resultCount">';
        if(this.isFiltered) {
            html += `<span>Filtered ${this.filteredCount} of ${this.totalCount} total</span>`;
        } else {
            html += `<span>Total results: ${this.totalCount}</span>`;
        }
        html += '</div>';
        document.querySelectorAll('.tablePagination').forEach((pagination) => {
            pagination.innerHTML = html;
        })
    }


    addPaginationEventListener()
    {
        let previousButtons = document.querySelectorAll('.prevButton');
        let nextButtons = document.querySelectorAll('.nextButton');
        let currentPage = 1;
        let sort = this.getActiveSort();

        if (previousButtons.length > 0) {
            let newCurrentPage = 1;
            previousButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    this.clearGoToPage();
                    let activePageElem = document.querySelectorAll('.activePagination');
                    activePageElem.forEach((activeElem) => {
                        currentPage = parseInt(activeElem.getAttribute('data-page'));
                        if (currentPage === 1) {
                            return;
                        }
                        newCurrentPage = `${currentPage - 1}`;
                        activeElem.setAttribute('data-page', newCurrentPage);
                        activeElem.innerText = newCurrentPage;
                    })
                    if (currentPage !== 1) {
                        this.populateTable(newCurrentPage, sort).then(() => {
                        });
                    }
                })
            })
        }

        if (nextButtons.length > 0) {
            let newCurrentPage = 1;
            let currentPage = 1;
            let maxPages = Math.ceil(this.totalCount / this.perPage);
            nextButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    this.clearGoToPage();
                    let activePageElem = document.querySelectorAll('.activePagination');
                    activePageElem.forEach((activeElem) => {
                        currentPage = parseInt(activeElem.getAttribute('data-page'));
                        if(this.filteredCount && this.isFiltered) {
                            maxPages = Math.ceil(this.filteredCount / this.perPage);
                        }
                        if (currentPage >= maxPages) {
                            return;
                        }
                        newCurrentPage = `${currentPage + 1}`;
                        activeElem.setAttribute('data-page', newCurrentPage);
                        activeElem.innerText = newCurrentPage;
                    });
                    if (currentPage < maxPages) {
                        this.populateTable(newCurrentPage, sort).then(() => {
                        });
                    }
                })
            })
        }
        if (document.querySelectorAll('.paginationButton').length > 0) {
            document.querySelectorAll('.paginationButton').forEach((elem) => {
                let newCurrentPage = 1;
                elem.addEventListener('click', () => {
                    let activePageElem = document.querySelectorAll('.activePagination');
                    newCurrentPage = elem.getAttribute('data-page');
                    activePageElem.forEach((activeElem) => {
                        activeElem.setAttribute('data-page', newCurrentPage);
                        activeElem.innerText = newCurrentPage;
                    });
                    this.populateTable(newCurrentPage, sort).then(() => {
                    });
                })
            })
        }
    }

    addGoToPageListener() {
        let inputs = document.querySelectorAll('.goToPageInput');
        inputs.forEach((input) => {
            let timeout = null;
           input.addEventListener('input', () => {
               if(input.value.trim() === '') {
                   return;
               }
               clearTimeout(timeout);
               timeout = setTimeout(() => {
                   let activePageElem = document.querySelectorAll('.activePagination');
                   activePageElem.forEach((elem) => {
                       if(input.value < 1) {
                           input.value = 1;
                       }
                       let totalCount = this.totalCount;
                       if(this.isFiltered) {
                           totalCount = this.filteredCount;
                       }
                       let maxPages = Math.ceil(totalCount / this.perPage);
                       if(input.value > maxPages) {
                           input.value = maxPages;
                       }
                       elem.setAttribute('data-page', input.value);
                       elem.innerText = input.value;
                   });
                   this.populateTable(input.value, this.getActiveSort()).then(() => {
                   });
               }, 350);
           });
        });
    }

    clearGoToPage() {
        let inputs = document.querySelectorAll('.goToPageInput');
        inputs.forEach((input) => {
            input.value = '';
        });
    }

    selectPerPageListener()
    {
        let self = this;
        document.getElementById('perPageSelector').addEventListener('change', function () {
            localStorage.setItem('limitSelect', this.value);
            self.setupTable(0, self.getActiveSort());
        })
    }

    getTableReadyEventName() {
        return this.tableReadyEventName;
    }

    setupTable(page = 0, sort = null) {
        if(!sort) {
            this.resetSortButtonsToDefault();
        }
        this.populateTable(page, sort).then(() => {
            this.generatePagination().then(() => {
                this.addPaginationEventListener();
                this.addGoToPageListener();
            });
        })
    }

    setPerPageValue() {
        document.getElementById('perPageSelector').value = localStorage.getItem('limitSelect') ?? '10';
    }

    initSort() {
        this.addSortListeners();
    }

    addSortListeners() {
        this.sortButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                this.sort(btn);
            });
        });
    }


    sort(button) {
        this.resetSortButtonsToDefault(button);
        if(!button.classList.contains('active')) {
            button.classList.add('active');
            this.setupTable(0, {
                direction: `${button.getAttribute('data-direction').toUpperCase()}`,
                column: `${button.getAttribute('data-columnname')}`
            });
            return;
        }
        this.changeSortingDirectionForButton(button);
        this.setupTable(0, {
            direction: `${button.getAttribute('data-direction').toUpperCase()}`,
            column: `${button.getAttribute('data-columnname')}`
        });
    }

    changeSortingDirectionForButton(button) {
        let sortingDirection = button.getAttribute('data-direction');
        button.setAttribute('data-direction', sortingDirection === 'desc' ? 'asc' : 'desc');
        button.innerHTML = button.getAttribute('data-direction') === 'asc' ? this.getAscendingHtmlIcon() : this.getDescendingHtmlIcon();
    }

    resetSortButtonsToDefault(clickedButton = null) {
        this.sortButtons.forEach((btn) => {
            if(clickedButton === btn) { // skip for the clicked button
                return;
            }
            btn.classList.remove('active');
            btn.innerHTML = this.getDescendingHtmlIcon();
        });
    }

    getAscendingHtmlIcon() {
        return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>`;
    }

    getDescendingHtmlIcon() {
        return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>`;
    }

    getActiveSort() {
        let activeSort = document.querySelectorAll('.sortColumn.active')[0];
        if(activeSort) {
            return {
                direction: `${activeSort.getAttribute('data-direction').toUpperCase()}`,
                column: `${activeSort.getAttribute('data-columnname')}`
            }
        }
        return null;
    }

}

export function initCrudTable(tableId, PageFactory) {
    let crudTableElement = document.getElementById(tableId);
    if (crudTableElement) {
        let filterData = {};
        if (crudTableElement.getAttribute('data-filter')) {
            filterData[crudTableElement.getAttribute('data-filter')] =
                crudTableElement.getAttribute('data-filter-value');
        }
        let crudTable = new TableView({
            crudTable: crudTableElement,
            crudTableBody: document.getElementById('tableData'),
            delayOnInputSearchInMs: 400,
            filterData: filterData
        });
        let page = crudTableElement.getAttribute('data-js-page');
        if(!page) {
            throw new Error(`The element with the id ${tableId} requires the data-js-page attribute`);
        }

        let pageFactory = new PageFactory(crudTable);
        console.log(page);
        let pageEntity = pageFactory.make(page);
        console.log(pageEntity);
        pageEntity.addCreateButtonListener();
        crudTable.setupTable();

        document.body.addEventListener(crudTable.getTableReadyEventName(), (e) => {
            pageEntity.addEditButtonListeners();
            pageEntity.addDeleteButtonListeners();
            pageEntity.addCopyAsDraftListeners();
            pageEntity.addCheckRowListeners();
        });
    } else {
        throw new Error(`Element with id ${tableId} not found.`);
    }
}