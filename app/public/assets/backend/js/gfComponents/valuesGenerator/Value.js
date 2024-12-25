import {valuesGeneratorSelectors} from "./valuesGeneratorSelectors.js";
import {valuesGeneratorAssets} from "./valuesGeneratorAssets.js";
import {valuesGeneratorEvents} from "./valuesGeneratorEvents.js";

export default class Value {

    container;

    input;

    valueView;

    removeButton;

    constructor(eventEmitter, value, viewValue, inputName = null, existingContainer = null, useViewValueAsKey = false) {
        this.eventEmitter = eventEmitter;
        this.value = value;
        this.viewValue = viewValue;
        this.useViewValueAsKey = useViewValueAsKey;
        if(!this.value) {
            this.value = this.viewValue;
        }
        if(!existingContainer) {
            this.inputName = inputName;
            this.generateView();
        } else {
            this.container = existingContainer;
            this.removeButton = this.container.querySelector(`.${valuesGeneratorSelectors.classes.removeButton}`);
            this.valueView = this.container.querySelector(`.${valuesGeneratorSelectors.classes.valueText}`);
            this.addRemoveButtonListener();
        }
    }

    generateView() {
       this.generateContainer();
       this.generateInput();
       this.generateValueView();
       this.generateRemoveButton();

       this.getContainer().appendChild(this.getInput());
       this.getContainer().appendChild(this.getValueView());
       this.getContainer().appendChild(this.getRemoveButton());
    }

    generateContainer() {
        this.container = document.createElement('div');
        this.container.classList.add(valuesGeneratorSelectors.classes.valueSingleContainer);
    }

    getContainer() {
        return this.container;
    }

    generateInput() {
        this.input = document.createElement('input');
        this.input.type = 'hidden';
        this.input.name = this.getInputName();
        this.input.value = this.getValue();
    }

    getUseViewValueAsKey() {
        return this.useViewValueAsKey;
    }

    getInputName() {
        return `${this.inputName}[new][${this.getUseViewValueAsKey() ? this.getViewValue() : ''}]`;
    }

    getValue() {
        return this.value;
    }

    getInput() {
        return this.input;
    }

    generateValueView() {
        this.valueView = document.createElement('span');
        this.valueView.classList.add(valuesGeneratorSelectors.classes.valueText);
        this.valueView.innerText = this.viewValue;
    }

    getViewValue() {
        return this.viewValue;
    }

    getValueView() {
        return this.valueView;
    }

    getValueText() {
        return this.getValueView().innerText;
    }

    generateRemoveButton() {
        this.removeButton = document.createElement('div');
        this.removeButton.classList.add(valuesGeneratorSelectors.classes.removeButton);
        this.removeButton.innerHTML = valuesGeneratorAssets.removeIcon;
        this.addRemoveButtonListener();
    }

    addRemoveButtonListener() {
        this.getRemoveButton().addEventListener('click', () => {
            const id = this.getRemoveButton().getAttribute(valuesGeneratorSelectors.attributes.id);
            this.getContainer().remove();
            this.eventEmitter.emit(valuesGeneratorEvents.valueDeleted, {id: id, value:this.getValueText()});
        });
    }

    getRemoveButton() {
        return this.removeButton;
    }

    getView() {
        return this.getContainer();
    }
}