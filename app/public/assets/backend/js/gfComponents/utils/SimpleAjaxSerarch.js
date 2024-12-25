export default class SimpleAjaxSearch {

    customPostData = {};
    constructor(props) {
        this.initiator = props.initiator;
        this.fetchUrl = this.initiator.dataset.action;
        this.placeholder = props.placeholder ?? 'Search';
        this.dispatchTank = props.dispatchTank;
        this.resultClickedEventName = props.resultClickedEventName;
        this.isSearchOpen = false;
        this.limit = props.limit ?? 10;
        this.charactersSearchThreshold = props.charactersSearchThreshold ?? 3;
        this.delayOnSearchInMs = props.delayOnSearchInMs ?? 200;
        this.resultViewParameter = props.resultViewParameter;
        this.noMoreResultsMessage = props.noMoreResultsMessage;
        this.filters = props.filters ?? false;
        this.offset = 0;
        this.isFetching = false;
        this.isNoMore = false;
    }


    mount() {
        this.setElements();
        this.assembleHTML();
        this.initiator.addEventListener('click', (e) => {
            this.removeForeignActiveSearchContainers();
            e.preventDefault();
            if(this.isSearchOpen) {
                return;
            }
            this.make();
        });
    }

    setCustomPostData(key, value) {
        this.customPostData[key] = value;
    }

    assembleHTML() {
        this.container.appendChild(this.searchInput);
        this.container.appendChild(this.resultsContainer);
    }


    setElements() {
        this.container = this.generateSearchContainer();
        this.searchInput = this.generateSearchInput();
        this.resultsContainer = this.generateResultsContainer();
        this.loader = this.generateLoader();
    }

    generateSearchContainer() {
        let container = document.createElement('div');
        container.classList.add('searchContainer');
        container.self = this;
        return container;
    }

    generateSearchInput() {
        let input = document.createElement('input');
        input.classList.add('searchInput', 'form-control');
        input.placeholder = this.placeholder;
        this.addInputSearchListener(input);
        return input;
    }

    generateResultsContainer() {
        let resultsContainer = document.createElement('div');
        resultsContainer.classList.add('resultsContainer');
        this.addScrollListener(resultsContainer);
        return resultsContainer;
    }
    decodeHtmlEntity = function(str) {
        return str.replace(/&#(\d+);/g, function(match, dec) {
            return String.fromCharCode(dec);
        });
    };

    generateLoader() {
        let loader = document.createElement('div');
        loader.classList.add('loaderAjaxSearch');
        return loader;
    }

    make() {
        this.initiator.appendChild(this.container);
        this.searchInput.focus();
        this.isSearchOpen = true;
    }


    destroy() {
        this.container.remove();
        this.clearResults();
        this.searchInput.value = '';
        this.isSearchOpen = false;
    }

    getParamsForSearch() {
        let formData = new FormData();
        formData.append('limit', this.limit);
        formData.append('offset', this.offset);
        formData.append('search', this.searchInput.value);
        formData.append('filter', JSON.stringify(this.filters));
        Object.entries(this.customPostData).forEach(([key, value]) => {
            formData.append(key, value.toString());
        });
        return formData;
    }

    async doAction() {
        const req = await fetch(this.fetchUrl, {
            method: 'POST',
            body: this.getParamsForSearch()
        });
        return await req.text();
    }

    addInputSearchListener(input) {
        let timeout = null;
        input.addEventListener('input', async () => {
            clearTimeout(timeout);
            if (input.value.trim() === '') {
                this.destroy();
            }
            timeout = setTimeout(async () => {
                if (input.value.trim().length >= this.charactersSearchThreshold && !this.isFetching) {
                    this.isFetching = true;
                    this.printLoader();
                    let results = await this.doAction();
                    this.clearResults();
                    let parsedResults = JSON.parse(results);
                    if(parsedResults.body.resultsFound === 0) {
                        this.printNoMore();
                    }
                    this.generateResults(parsedResults.body.data);
                    this.isFetching = false;
                    this.removeLoader();
                }
            }, this.delayOnSearchInMs);
        });
    }

    addScrollListener(container) {
        container.addEventListener('scroll', async () => {
            if(this.isFetching || this.isNoMore) {
                return;
            }
            if (container.offsetHeight + container.scrollTop >= container.scrollHeight) {
                this.offset += this.limit;
                this.isFetching = true;
                this.printLoader();
                let results = await this.doAction();
                let parsedResults = JSON.parse(results);
                if(parsedResults.body.resultsFound === 0) {
                    this.printNoMore();
                }
                this.generateResults(parsedResults.body.data);
                this.isFetching = false;
                this.removeLoader();
            }
        });
    }

    generateResults(entities) {
        this.resultsContainer.classList.add('show');
        entities.forEach((entity) => {
            this.resultsContainer.appendChild(this.generateResult(entity));
        });
    }

    generateResult(entity) {
        let result = document.createElement('div');
        result.classList.add('searchResult');
        result.entityData = entity;
        result.innerText = this.decodeHtmlEntity(entity[this.resultViewParameter]);
        this.addResultClickListener(result);
        return result;
    }

    clearResults() {
        this.resultsContainer.classList.remove('show');
        this.resultsContainer.innerHTML = '';
        this.isNoMore = false;
        this.isFetching = false;
        this.offset = 0;
    }

    addResultClickListener(element) {
        element.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.destroy();
            this.dispatchTank.dispatchEvent(new CustomEvent(this.resultClickedEventName, {detail: element.entityData}));
        });
    }

    printNoMore() {
        let message = document.createElement('div');
        message.classList.add('alert-danger');
        message.innerText = this.noMoreResultsMessage;
        this.resultsContainer.appendChild(message);
        this.isNoMore = true;
    }

    printLoader() {
        if(!this.resultsContainer.querySelector('.loaderAjaxSearch')) {
            this.resultsContainer.appendChild(this.loader);
        }
    }

    removeLoader() {
        this.loader.remove();
    }

    removeForeignActiveSearchContainers() {
        let allSearchContainers = document.querySelectorAll('.searchContainer');
        allSearchContainers.forEach((container) => {
            if(container !== this.container) {
                container.self.destroy();
            }
        });
    }


}