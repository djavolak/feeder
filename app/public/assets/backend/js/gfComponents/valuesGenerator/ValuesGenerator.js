import {valuesGeneratorSelectors} from "./valuesGeneratorSelectors.js";
import Value from "./Value.js";
import EventEmitter from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/EventEmitter/EventEmitter.js";
import {valuesGeneratorEvents} from "./valuesGeneratorEvents.js";
import {blockSelectors} from "../postEditor/Blocks/blockSelectors.js";

export default class ValuesGenerator {
    constructor(container, form, customContainerData = {}, generateInputsOnDelete = true,
                useViewValueAsKey = false) {
        this.container = container;
        this.customContainerData = customContainerData;
        this.generateInputsOnDelete = generateInputsOnDelete;
        this.useViewValueAsKey = useViewValueAsKey;
        if(!this.container) {
            this.container = this.generateContainer();
        }
        this.mainInput = this.container.querySelector(`.${valuesGeneratorSelectors.classes.mainInput}`);
        this.valuesContainer = this.container.querySelector(`.${valuesGeneratorSelectors.classes.valuesContainer}`);
        this.valueInputName = this.container.getAttribute(valuesGeneratorSelectors.attributes.name);
        this.form = form ?? null;
        this.eventEmitter = new EventEmitter();
        this.values = new Map();
        this.page = 0;
        this.inputDelay = 300;
        this.limit = 10;
        this.isFetching = false;
        this.searchTimeout = null;
    }

    init() {
        this.initExistingValues();
        this.addMainInputListener();
        this.preventFormSubmitOnEnterIfMainInputInFocus();
        this.addValueDeletedListener();
    }

    getUseViewValueAsKey() {
        return this.useViewValueAsKey;
    }
    getCustomContainerData() {
        return this.customContainerData;
    }

    getGenerateInputsOnDelete() {
        return this.generateInputsOnDelete;
    }

    initExistingValues() {
        const existingContainers = this.getValuesContainer()
            .querySelectorAll(`.${valuesGeneratorSelectors.classes.valueSingleContainer}`);
        if(existingContainers && existingContainers.length > 0) {
            existingContainers.forEach((existingValueContainer) => {
                const viewValue = existingValueContainer.querySelector(`.${valuesGeneratorSelectors.classes.valueText}`);
                const value = existingValueContainer.querySelector('input')?.value;
                this.generateValue(viewValue.innerText, value, existingValueContainer);
            })
        }
    }

    addMainInputListener() {
        this.getMainInput().addEventListener('keyup', (e) => {
            const mainInputValue = this.getMainInput().value.trim();
            if(e.key === 'Enter' && mainInputValue.length > 0 &&
                this.getContainer().getAttribute(valuesGeneratorSelectors.attributes.canCreate) !== 'false') {
                this.generateValue(mainInputValue);
                this.clearMainInput();
                this.closeSearchResults();
            }
        });

        const searchHandler = this.getMainInput().getAttribute(valuesGeneratorSelectors.attributes.searchHandler);
        if(searchHandler) {
            this.getMainInput().addEventListener('input',(e) => {
                this.handleSearch(searchHandler);
            });
        }
    }

    handleSearch(searchHandler) {
        clearTimeout(this.searchTimeout);
        if(this.getMainInput().value.trim().length > 0) {
            this.searchTimeout = setTimeout(async () => {
                if(this.isFetching) {
                    return;
                }
                const data = await this.fetchData(this.getMainInput().value.trim(), searchHandler);
                this.closeSearchResults();
                this.openSearchResults(data);
            }, this.getInputDelay());
        } else {
            this.closeSearchResults();
        }
    }

    openSearchResults(data) {
        if(this.getMainInput().value.trim().length === 0) {
            return;
        }
        const container = this.generateResultsContainer();
        const results = this.generateResults(data);
        if(results) {
            container.appendChild(results);
            this.getContainer().appendChild(container);
        }
    }

    generateResultsContainer() {
        const container = document.createElement('div');
        container.classList.add(valuesGeneratorSelectors.classes.searchResultsContainer);
        return container;
    }

    generateResults(data) {
        const fragment = document.createDocumentFragment();
        if(data && data.entities && data.entities.data.length > 0) {
            const searchFieldValue = this.getMainInput().getAttribute(valuesGeneratorSelectors.attributes.searchFieldValue);
            const searchFieldViewValue = this.getMainInput().getAttribute(valuesGeneratorSelectors.attributes.searchFieldViewValue);
            data.entities.data.forEach((entity) => {
                let value = null;
                let viewValue = null;
                if(entity.columns && searchFieldValue && entity.columns[searchFieldValue]) {
                    if(typeof entity.columns[searchFieldValue] === 'object' && entity.columns[searchFieldValue].value) {
                        value = entity.columns[searchFieldValue].value;
                    } else {
                        value = entity.columns[searchFieldValue] ?? null;
                    }
                }
                if(entity.columns && searchFieldViewValue && entity.columns[searchFieldViewValue]) {
                    if(typeof entity.columns[searchFieldViewValue] === 'object' && entity.columns[searchFieldViewValue].value) {
                        viewValue = entity.columns[searchFieldViewValue].value;
                    } else {
                        viewValue = entity.columns[searchFieldViewValue] ?? null;
                    }
                }
                if(value && viewValue) {
                   fragment.appendChild(this.generateResult(value, viewValue));
                }
            });
        } else {
            return null;
        }
        return fragment;
    }

    generateResult(value, viewValue) {
        const result = document.createElement('div');
        result.innerText = viewValue;
        result.setAttribute(valuesGeneratorSelectors.attributes.searchResultValue, value.toString());
        result.classList.add(valuesGeneratorSelectors.classes.searchResult);
        result.addEventListener('click', (e) => {
            this.generateValue(viewValue, value);
            this.closeSearchResults();
            this.clearMainInput();
        });
        return result;
    }

    closeSearchResults() {
        const searchContainer = this.getContainer().querySelector(`.${valuesGeneratorSelectors.classes.searchResultsContainer}`);
        if(searchContainer) {
            searchContainer.remove();
        }
        this.page = 0;
    }

    preventFormSubmitOnEnterIfMainInputInFocus() {
        if(this.form) {
            this.form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && document.activeElement === this.mainInput) {
                    e.preventDefault();
                }
            });
        }
    }

    addValueDeletedListener() {
        this.eventEmitter.on(valuesGeneratorEvents.valueDeleted, (data) => {
            if(data && data.value) {
                if (this.getValues().get(data.value.toString().toLowerCase())) {
                    if(data.id && this.getGenerateInputsOnDelete()) {
                        this.getContainer().appendChild(this.generateDeletedValueInput(data.id));
                    }
                    this.getValues().delete(data.value.toString().toLowerCase());
                }
            }
        });
    }

    generateDeletedValueInput(id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.value = id;
        input.name = `${this.getValueInputName()}[deleted][]`;
        return input;
    }

    generateValue(viewValue, value, container = null) {
        if(this.getValues().get(viewValue.toString().toLowerCase())) {
            alert(`${viewValue} already exists`);
            return;
        }
        const valueObj = new Value(this.eventEmitter, value, viewValue, this.getValueInputName(), container, this.getUseViewValueAsKey());
        if(!container) {
            this.getValuesContainer().appendChild(valueObj.getView());
        }

        this.getValues().set(viewValue.toString().toLowerCase(), valueObj);
    }

    getInputDelay() {
        return this.inputDelay;
    }

    getValues() {
        return this.values;
    }

    clearMainInput() {
        this.mainInput.value = '';
    }

    getContainer() {
        return this.container;
    }

    getMainInput() {
        return this.mainInput;
    }

    getValuesContainer() {
        return this.valuesContainer;
    }

    getValueInputName() {
        return this.valueInputName;
    }

    static getAllContainersForInit(form) {
        return form.querySelectorAll(`.${valuesGeneratorSelectors.classes.container}`);
    }

    async fetchData(search, fetchPath) {
        this.isFetching = true;
        let response = await fetch(fetchPath, {
            method: 'POST',
            body: this.getParamsForSearch(search)
        });
        response = await response.text();
        this.isFetching = false;
        return JSON.parse(response);
    }

    getParamsForSearch(search) {
        let formData = new FormData();
        formData.append('filter[page]', (this.page++).toString());
        formData.append('offset', ((this.page > 1 ? ((this.page - 1) * this.limit) : 0).toString()));
        formData.append('search', `%${search}%`);
        return formData;
    }

    generateContainer() {
        const container = document.createElement('div');
        container.classList.add(valuesGeneratorSelectors.classes.container);
        container.setAttribute(valuesGeneratorSelectors.attributes.name, this.getCustomContainerData().inputName);
        container.setAttribute(valuesGeneratorSelectors.attributes.canCreate, 'false');
        const label = document.createElement('label');
        label.innerText = this.getCustomContainerData().label;
        container.appendChild(label);
        const input = document.createElement('input');
        input.classList.add('valuesGeneratorInput');
        input.setAttribute(valuesGeneratorSelectors.attributes.searchHandler, this.getCustomContainerData().action);
        input.setAttribute(valuesGeneratorSelectors.attributes.searchFieldValue,
            this.getCustomContainerData().searchFieldValue);
        input.setAttribute(valuesGeneratorSelectors.attributes.searchFieldViewValue,
            this.getCustomContainerData().searchFieldViewValue);
        input.classList.add(blockSelectors.classes.input);
        container.appendChild(input);
        const valuesContainer = document.createElement('div');
        valuesContainer.classList.add('valuesContainer');
        container.appendChild(valuesContainer);
        return container;
    }

}