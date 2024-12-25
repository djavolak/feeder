export default class TheSimplestAjaxSearch {

    inputType = null;
    customEvent = new CustomEvent('resultSelected', {detail: {result: null}});
    hiddenInputName = null;

    minInputLength = 2;
    inputDelay = 300;

    limit = '20';
    offset = '0';
    isFetching = false;

    constructor(inputs, classSelector = true) {
        if(classSelector) {
            this.inputs = document.querySelectorAll(`.${inputs}`);
        } else {
            this.inputs = inputs;
        }
    }

    init() {
        this.inputs.forEach(input => {
            this.hiddenInputName = input.dataset.inputname;
            if (input.tagName === 'SELECT') {
                this.handleSelectStuff(input);
            }
            if (input.tagName === 'input') {
                this.handleTextInputStuff(input)
            }
        });
    }

    handleTextInputStuff(input) {
        this.inputType = 'input';
        input.addEventListener('input', () => {

        });
    }

    handleSelectStuff(select) {
        this.inputType = 'select';
        this.appendTriggerIcon(select);
        select.addEventListener('change', () => {

        });
    }

    resultSelectedCallback(name, id, input, resultWrapper) {
        switch (input.tagName) {
            case 'SELECT':
                input.value = id;
                input.dispatchEvent(this.customEvent);
                input.dispatchEvent(new Event('change'));
                break;
        }
        this.closeResults(resultWrapper);
    }

    async fetchData(search, filters, fetchPath) {
        this.isFetching = true;
        let response = await fetch(fetchPath, {
            method: 'POST',
            body: this.getParamsForSearch(search, filters)
        });
        response = await response.text();
        this.isFetching = false;
        return JSON.parse(response);
    }

    generateResults(data, wrapper, input) {
        let entities = data.body.data;
        wrapper.querySelector('ul')?.remove();
        let resultWrapper = document.createElement('ul');
        resultWrapper.style.listStyle = 'none';
        resultWrapper.style.padding = '0.5rem';
        resultWrapper.style.border = '1px solid #ccc';
        resultWrapper.style.margin = '0';
        resultWrapper.style.color = 'black';
        resultWrapper.style.backgroundColor = 'white';
        resultWrapper.style.position = 'absolute';
        resultWrapper.style.top = '200%';
        resultWrapper.style.width = input.getBoundingClientRect().width + 'px';
        resultWrapper.style.zIndex = '99';
        entities.forEach(entity => {
            let name = entity[input.getAttribute('data-name-param')];
            let id = entity[input.getAttribute('data-id-param')];
            resultWrapper.appendChild(this.generateResultItem(name, id, input, resultWrapper));
        });
        wrapper.appendChild(resultWrapper);
    }

    getParamsForSearch(search, filters) {
        let formData = new FormData();
        formData.append('limit', this.limit);
        formData.append('offset', this.offset);
        formData.append('search', search);
        formData.append('filter', filters);
        return formData;
    }
    generateResultItem(name, id, input, resultWrapper) {
        let resultItem = document.createElement('li');
        resultItem.style.cursor = 'pointer';
        resultItem.innerHTML = name;
        resultItem.onmouseenter = () => {
            resultItem.style.backgroundColor = '#4e73df';
            resultItem.style.color = 'white';
        };
        resultItem.onmouseleave = () => {
            resultItem.style.backgroundColor = 'white';
            resultItem.style.color = 'black';
        };
        resultItem.addEventListener('click', () => {
            this.resultSelectedCallback(name, id, input, resultWrapper);
        });
        return resultItem;
    }

    appendTriggerIcon(select) {
        let wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'flex';
        wrapper.style.alignItems = 'center';
        wrapper.style.gap = '10px';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        let svg = `<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="pointer-events: none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                  </svg>`;
        let svgContainer = document.createElement('span');
        svgContainer.style.cursor = 'pointer';
        svgContainer.innerHTML = svg;
        svgContainer.addEventListener('click', () => {
            this.generateInputForSearch(wrapper, select);
        });
        wrapper.appendChild(svgContainer);
    }

     closeSearch(input) {
        input.remove();
    }

    closeResults(resultWrapper) {
        resultWrapper.remove();
    }

    generateInputForSearch(wrapper, select) {
        let inputForSearch = document.createElement('input');
        inputForSearch.style.position = 'absolute';
        inputForSearch.style.zIndex = '99';
        inputForSearch.classList.add('form-control');
        inputForSearch.style.top = '100%';
        inputForSearch.style.width = select.getBoundingClientRect().width + 'px';
        inputForSearch.addEventListener('blur', (e) => {
            this.closeSearch(inputForSearch);
            setTimeout(() => {
                wrapper.querySelector('ul')?.remove();
            }, 600);
        });
        inputForSearch.addEventListener('input', async () => {
            if (inputForSearch.value.length >= this.minInputLength) {
                let data = await this.fetchData(inputForSearch.value, (select.getAttribute('data-filter') ?? '{}'), select.getAttribute('data-fetch-path'));
                this.generateResults(data, wrapper, select);
            }
        });
        wrapper.appendChild(inputForSearch);
        inputForSearch.focus();
    }
}