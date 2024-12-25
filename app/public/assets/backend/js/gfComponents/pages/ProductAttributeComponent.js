export default class ProductAttributeComponent {
    constructor(
        attributeId,
        attributeName,
        attributeValues,
        attributeDeletedEventName,
        regenerateSecondDescriptionEventName,
        index,
        form,
        existingAttributeValues,
        position,
        addedAfterLoad) {
        this.attributeId = attributeId ?? null;
        this.attributeName = attributeName ?? null;
        this.attributeValues = attributeValues?.attributeValues ?? null;
        this.attributeDeletedEventName = attributeDeletedEventName ?? null;
        this.regenerateSecondDescriptionEventName = regenerateSecondDescriptionEventName ?? null;
        this.attributeContainer = document.getElementById('attributes');
        this.selectedAttributeValues = {};
        this.index = index ?? null;
        this.isNewAttribute = this.attributeId === null;
        this.form = form ?? null;
        this.existingAttributeValues = existingAttributeValues ?? null;
        this.position = position ?? '';
        this.addedAfterLoad = addedAfterLoad ?? false
        this.allowedListOfAttributesForSecondDescription =
            document.getElementById('attributes')?.getAttribute('data-allowed-attribute-ids')?.split(',');
        this.isAllowedForSecondDescription = this.allowedListOfAttributesForSecondDescription.includes(this.attributeId?.toString());
        this.init();
    }

    init() {
        this.generateViewAndListeners();
    }

    dispatchAllowedAttributeIdForSecondDescriptionChanged() {
        if(this.isAllowedForSecondDescription) {
            this.attributeContainer.dispatchEvent(new CustomEvent(this.regenerateSecondDescriptionEventName));
        }
    }

    generateViewAndListeners() {
        let template = document.getElementById('productAttributeComponent');
        this.container = template.content.cloneNode(true).querySelector('.productAttributeComponent');
        if(this.position !== '') {
            this.container.setAttribute('data-position', this.position);
        }
        this.header = this.container.querySelector('header');
        this.contentBody = this.container.querySelector('.contentBody');
        this.headerName = this.header.querySelector('.name');
        this.deleteButton = this.header.querySelector('.delete');
        this.searchValuesInput = this.contentBody.querySelector('.attributeValueSearch');
        this.attributeValuesList = this.contentBody.querySelector('.attributeValuesList');
        this.createNewAttributeValueFromFormButton = this.contentBody.querySelector('.createNewAttributeValueFromForm');
        this.populateContent();
        this.addListeners();
        if(this.position !== '' && this.addedAfterLoad) {
            this.appendByPosition(this.container);
        } else {
            this.attributeContainer.appendChild(this.container);
        }

    }

    appendByPosition(container) {
        let elementsWithPosition = document.querySelectorAll('.productAttributeComponent[data-position]');
        if(elementsWithPosition.length === 0) { // If no elements with data-position are present, append to end.
            this.attributeContainer.appendChild(container);
            return;
        }
        let lowest = null;
        let inserted = false;
        elementsWithPosition.forEach((element) => {
            if(inserted) { // Return if we've already inserted the element
                return;
            }
            let elemPosition = parseInt(element.getAttribute('data-position'));
            if(elemPosition < parseInt(this.position)) { // If data-position of the element is lower than ours, save as lowest
                lowest = element;
            }

            if(elemPosition > parseInt(this.position)) { // If data-position of the element is higher than ours
                if(lowest === null) { // If no elements have a lower position than ours insert as first element
                    this.attributeContainer.prepend(container);
                    inserted = true;
                } else {  // If we have an element with the lowest position saved, insert after the saved element
                    this.attributeContainer.insertBefore(container, lowest.nextSibling);
                    inserted = true;
                }
            }
        });
        // If we have an element saved as lowest, but we haven't inserted yet
        if(!inserted && lowest !== null) {
            lowest.after(container);
            inserted = true;
        }
    }

    populateContent() {
        if(this.isNewAttribute) {
            this.headerName.appendChild(this.generateNewAttributeNameInput());
            this.createNewAttributeValueFromFormButton.remove();
            return;
        }
        this.headerName.innerText = this.attributeName;
        if (this.position !== '') {
            this.headerName.innerText += ' [' + this.position + ']';
        }
        if(this.existingAttributeValues) {
            for (let key of Object.keys(this.existingAttributeValues)) {
                let reference = this.existingAttributeValues[key];
                this.generateResultCallback(reference.attributeId, reference.attributeValueId, reference.attributeValue);
                this.selectedAttributeValues[reference.attributeValueId] = reference.attributeValue;
            }
        }
    }

    addListeners() {
        this.addHeaderListener();
        this.addSearchListener();
        this.addCreateNewAttributeFromForm();
    }

    addCreateNewAttributeFromForm() {
        this.createNewAttributeValueFromFormButton.addEventListener('click', async (e) => {
            e.preventDefault();
            if(this.searchValuesInput.value.trim().length === 0) {
                return;
            }
            this.attributeValuesList.style.opacity = '0.3';
            this.startLoaderForAttributeValues(this.container, 'large');
            let res = await this.createNewAttributeValueFromForm(this.searchValuesInput.value);
            if (res.message) {
                this.searchValuesInput.value = '';
                this.resultsDestroy();
                this.attributeValuesList.style.opacity = '1';
                this.stopLoaderForAttributeValues();
                alert(res.message);
                return;
            }
            this.generateResultCallback(this.attributeId, res.attributeValueId, this.searchValuesInput.value);
            this.selectedAttributeValues[res.attributeValueId] = this.searchValuesInput.value;
            this.attributeValues.push({
                attributeValueId: res.attributeValueId.toString(),
                attributeId: this.attributeId.toString(),
                attributeValue: this.searchValuesInput.value
            })
            this.searchValuesInput.value = '';
            this.resultsDestroy();
            this.attributeValuesList.style.opacity = '1';
            this.stopLoaderForAttributeValues();
            this.dispatchAllowedAttributeIdForSecondDescriptionChanged();
        });
    }

    async createNewAttributeValueFromForm(value) {
        let action = '/attribute/createAttributeValue/';
        let data = new FormData();
        data.append('attributeId', this.attributeId);
        data.append('attributeValue', value);
        let req = await fetch(action, {
            method: 'POST',
            body: data
        });
        return JSON.parse(await req.text());
    }

    addHeaderListener() {
        this.header.addEventListener('click', (e) => {
            if(e.target !== this.deleteButton && !e.target.classList.contains('newAttributeName')) {
                this.container.classList.toggle('expand');
            }
            this.resultsDestroy();
        });

        this.deleteButton.addEventListener('click', () => {
          this.deleteComponent();
        });
    }

    deleteComponent() {
        this.container.remove();
        this.attributeContainer.dispatchEvent(new CustomEvent(this.attributeDeletedEventName, {detail: this.attributeId}));
        this.dispatchAllowedAttributeIdForSecondDescriptionChanged();
    }

    addSearchListener() {
        if(!this.isNewAttribute) {
            this.searchValuesInput.addEventListener('click', () => {
                this.generateResults();
            });

            this.searchValuesInput.addEventListener('input', () => {
                this.filterResults(this.searchValuesInput.value, this.contentBody.querySelectorAll('.attributeResults p'));
            });
        }

        if(this.isNewAttribute) {
            this.searchValuesInput.addEventListener('keyup', (e) => {
                if(e.key === 'Enter' && this.searchValuesInput.value.trim().length > 0) {
                    this.generateNewAttributeRepresentation();
                    this.searchValuesInput.value = '';
                }
            });

            this.searchValuesInput.addEventListener('focus', () => {
                this.form.disableEnterSubmit = true;
            });

            this.searchValuesInput.addEventListener('blur', () => {
                this.form.disableEnterSubmit = false;
            });
        }
    }

    generateNewAttributeRepresentation() {
        let inputContainer = document.createElement('div');
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = `newAttributes[${this.index}][values][]`;
        input.value = this.searchValuesInput.value;
        inputContainer.appendChild(input);
        let inputRepresentation = document.createElement('div');
        inputRepresentation.classList.add('inputRepresentation');
        inputRepresentation.innerText = this.searchValuesInput.value;
        inputRepresentation.appendChild(this.generateDeleteRepresentation());
        inputContainer.appendChild(inputRepresentation);
        this.attributeValuesList.appendChild(inputContainer);
    }

    generateNewAttributeNameInput() {
        let input = document.createElement('input');
        input.name = `newAttributes[${this.index}][name]`;
        input.type = 'text';
        input.classList.add('newAttributeName','form-control');
        input.placeholder = 'Attribute Name';
        return input;
    }

    generateResults() {
        if(!this.contentBody.querySelector('.attributeResults')) {
            let resultsContainer = document.createElement('div');
            resultsContainer.classList.add('attributeResults', 'scrollableContainerCustomHeight');
            this.attributeValues.forEach((attributeValue) => {
               resultsContainer.appendChild(this.generateResult(attributeValue));
            });
            this.contentBody.appendChild(resultsContainer);
        }
    }

    generateResult(attributeValue) {
        let result = document.createElement('p');
        let attributeId = attributeValue.attributeId;
        let attributeValueId = attributeValue.attributeValueId;
        let attributeName = attributeValue.attributeValue;
        result.innerText = attributeName;
        if(this.selectedAttributeValues[attributeValueId]) {
            result.classList.add('disabled');
        }
        result.addEventListener('click', () => {
            this.generateResultCallback(attributeId, attributeValueId, attributeName);
            this.resultsDestroy();
            this.selectedAttributeValues[attributeValueId] = attributeName;
            this.dispatchAllowedAttributeIdForSecondDescriptionChanged();
        });
        return result;
    }

    generateResultCallback(attributeId, attributeValueId, attributeName) {
        let inputContainer = document.createElement('div');

        let attributeNameInput = document.createElement('input');
        attributeNameInput.type = 'hidden';
        attributeNameInput.name = `attribute[${this.attributeId}][][name]`;
        attributeNameInput.value = this.attributeName;
        inputContainer.appendChild(attributeNameInput);
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = `attribute[${attributeId}][][value]`;
        input.value = attributeName + '#' + attributeValueId;
        inputContainer.appendChild(input);
        let inputRepresentation = document.createElement('div');
        inputRepresentation.classList.add('inputRepresentation');
        inputRepresentation.innerText = attributeName;
        inputRepresentation.appendChild(this.generateDeleteRepresentation(attributeValueId));
        inputContainer.appendChild(inputRepresentation);
        this.attributeValuesList.appendChild(inputContainer);
    }

    generateDeleteRepresentation(attributeValueId = null) {
        let deleteRepresentation = document.createElement('div');
        deleteRepresentation.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                           </svg>`;
        deleteRepresentation.addEventListener('click', () => {
            deleteRepresentation.parentElement.parentElement.remove();
            if (attributeValueId) {
                delete this.selectedAttributeValues[attributeValueId];
                this.dispatchAllowedAttributeIdForSecondDescriptionChanged();
            }
        });
        return deleteRepresentation;
    }

    filterResults(value, targets) {
        targets.forEach((target) => {
            if(this.transliterate(target.innerText).toLowerCase().includes(value.toLowerCase())) {
                target.style.display = 'block';
            } else {
                target.style.display = 'none';
            }
        });
    }

    resultsDestroy() {
        let resultsContainer = this.contentBody.querySelector('.attributeResults');
        if(resultsContainer) {
            resultsContainer.remove();
        }
    }

    transliterate(string) {
        return string
            .replace(/ž/g, 'z')
            .replace(/š/g, 's')
            .replace(/đ/g, 'dj')
            .replace(/č/g, 'c')
            .replace(/ć/g, 'c')
            .replace(/Ž/g, 'z')
            .replace(/Š/g, 's')
            .replace(/Đ/g, 'dj')
            .replace(/Č/g, 'c')
            .replace(/Ć/g, 'c');
    }

    startLoaderForAttributeValues(container, loaderSize) {
        if(!document.getElementById('attributeValueLoader')) {
            container.appendChild(this.generateLoaderForAttributeValues(loaderSize));
        }
    }

    stopLoaderForAttributeValues() {
        let loader = document.getElementById('attributeValueLoader');
        if(loader) {
            loader.remove();
        }
    }

    generateLoaderForAttributeValues(loaderSize) {
        let loader = document.createElement('div');
        loader.id = 'attributeValueLoader';
        loader.classList.add(loaderSize);
        return loader;
    }
}