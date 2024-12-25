export default class MultipleSelectWithSearch {
    limit = '10';
    offset = '0';
    filters = {};
    isFetching = false;

    selectedItems = new Map();

    constructor(initiator, callBackFilter = null) {
        this.initiator = document.getElementById(initiator);
        this.container = this.initiator.parentElement;
        this.inputContainer = this.setInputContainer();
        this.fetchUrl = this.initiator.dataset.action;
        this.callbackFilter = callBackFilter;
    }

    setLimit(limit) {
        this.limit = limit;
    }

    setOffset(offset) {
        this.offset = offset;
    }

    setFilters(filters) {
        this.filters = filters;
    }

    init() {
        this.container.style.positon = 'relative';
        this.addSearchListener();
        this.addOnLoadListeners();
    }

    addSearchListener() {
        this.initiator.addEventListener('input', async (e) => {
            if (this.initiator.value.length < 2) {
                if (document.querySelector('.resultsContainer')) {
                    document.querySelector('.resultsContainer').remove();
                }
            }
            if (this.initiator.value.length >= 2 && !this.isFetching) {
                this.isFetching = true;
                let response = await fetch(this.fetchUrl, {
                    method: 'POST',
                    body: this.getParamsForSearch()
                });
                response = await response.text();
                this.generateResults(await JSON.parse(response));
                this.isFetching = false;
            }
        });
    }

    getParamsForSearch() {
        let formData = new FormData();
        formData.append('limit', this.limit);
        formData.append('offset', this.offset);
        formData.append('search', this.initiator.value);
        formData.append('filter', JSON.stringify(this.filters));
        return formData;
    }

    generateResults(results) {
        let resultsContainer = document.createElement('div');
        resultsContainer.classList.add('resultsContainer');
        let list = document.createElement('ul');
        let printed = false;
        if (results.body.resultsFound !== 0) {
            results.body.data.forEach((result) => {
                let resultItem = document.createElement('li');
                if (this.selectedItems.has(result.id.toString())) {
                    return;
                }
                resultItem.classList.add('resultItem');
                resultItem.innerHTML = result.name;
                resultItem.result = result;
                resultItem.addEventListener('click', () => this.resultItemClick(resultItem));
                list.appendChild(resultItem);
                printed = true;
            });
        }
        if (!printed) {
            let noResults = document.createElement('li');
            noResults.innerText = 'No results found';
            list.appendChild(noResults);
        }
        resultsContainer.appendChild(list);
        if (!document.querySelector('.resultsContainer')) {
            this.container.appendChild(resultsContainer);
        } else {
            document.querySelector('.resultsContainer').remove();
            this.container.appendChild(resultsContainer);
        }
    }

    resultItemClick(resultItem) {
        if (typeof this.callbackFilter === 'function') {
            if (!this.callbackFilter(resultItem)) {
                return false;
            }
        }
        resultItem.parentElement.remove();
        this.selectedItems.set(resultItem.result.id.toString(), resultItem.result.id);
        this.initiator.value = '';
        let container = document.createElement('div');
        container.resultId = resultItem.result.id;
        let nameSpan = document.createElement('span');
        nameSpan.innerText = resultItem.result.name;
        container.appendChild(nameSpan);
        let svg = `<svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                         fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>`;
        let svgContainer = document.createElement('span');
        svgContainer.classList.add('removeResult');
        svgContainer.innerHTML = svg;
        svgContainer.dataset.id = resultItem.result.id;
        svgContainer.addEventListener('click', () => this.removeResult(container));
        container.appendChild(svgContainer);
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = this.inputContainer.dataset.name;
        input.value = resultItem.result.id;
        container.appendChild(input);
        this.inputContainer.appendChild(container);
    }

    removeResult(container) {
        container.remove();
        this.selectedItems.delete(container.resultId.toString());
    }


    setInputContainer() {
        let containers = document.querySelectorAll('.searchInputs');
        let _container = null;
        containers.forEach((container) => {
            if (container.dataset.searchid === this.initiator.id) {
                _container = container;
            }
        });
        return _container;
    }

    addOnLoadListeners() {
        let results = document.querySelectorAll('.removeResult');
        results.forEach((result) => {
            let parentElem = result.parentElement;
            parentElem.resultId = result.dataset.id;
            result.addEventListener('click', () => this.removeResult(parentElem));
            this.selectedItems.set(result.dataset.id, result.dataset.id);
        });
    }
}