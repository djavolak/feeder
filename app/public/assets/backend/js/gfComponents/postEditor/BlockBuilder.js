import {blockSelectors} from "./Blocks/blockSelectors.js";

export default class BlockBuilder {
   constructor(containerClassName) {
       this.containerClassName = containerClassName;
       this.generateContainer();
       this.elements = [];
   }

   generateContainer() {
       this.container = document.createElement('div');
       this.container.classList.add(this.containerClassName);
   }

   generateSelect(label, optionData = {}, inputName, defaultValue = null) {
       const container = this.generateContainerWithLabel(label);
       const select = document.createElement('select');
       select.name = inputName;
       select.classList.add(blockSelectors.classes.input);
       if(defaultValue) {
           const option = document.createElement('option');
           option.value = defaultValue.value;
           option.innerText = defaultValue.label;
           select.appendChild(option);
       }
       Object.keys(optionData).forEach((key) => {
           const option = document.createElement('option');
           option.value = key;
           option.innerText = optionData[key];
           select.appendChild(option);
       });
       container.appendChild(select);
       this.elements.push(select);
       this.container.appendChild(container);
       return this;
   }

   generateContainerWithLabel(label) {
       const container = document.createElement('div');
       container.classList.add(blockSelectors.classes.inputContainer);
       if(label) {
           const labelElement = document.createElement('label');
           labelElement.innerText = label;
           container.appendChild(labelElement);
       }
       return container;
   }

   generateInput(label, inputName, type = null, placeholder = null) {
       const container = this.generateContainerWithLabel(label);
       const input = document.createElement('input');
       input.name = inputName;
       input.classList.add(blockSelectors.classes.input);
       if(type) {
           input.type = type;
       }
       if(placeholder) {
           input.placeholder = placeholder;
       }
       container.appendChild(input);
       this.elements.push(input);
       this.container.appendChild(container);
       return this;
   }

   generateTextarea(label, inputName, placeholder = null, rows = null, cols = null) {
       const container = this.generateContainerWithLabel(label);
       const input = document.createElement('textarea');
       input.name = inputName;
       input.classList.add(blockSelectors.classes.input);
       if(placeholder) {
           input.placeholder = placeholder;
       }
       if(rows) {
           input.setAttribute('rows', rows);
       }
       if(cols) {
           input.setAttribute('cols', cols);
       }
       container.appendChild(input);
       this.elements.push(input);
       this.container.appendChild(container);
       return this;
   }

    generateButton(className, label) {
        const button = document.createElement('div');
        button.classList.add(className);
        button.innerText = label;
        this.elements.push(button);
        this.container.appendChild(button);
        return this;
    }

    generateButtonWithContainer(containerClassName, buttonClassName, label) {
        const container = document.createElement('div');
        container.classList.add(containerClassName);
        const button = document.createElement('div');
        button.classList.add(buttonClassName);
        button.innerText = label;
        container.appendChild(button);
        this.elements.push(button);
        this.container.appendChild(container);
        return this;
    }

    generateBasicContainer(className) {
       const container = document.createElement('div');
       container.classList.add(className);
       this.elements.push(container);
       this.container.appendChild(container);
       return this;
    }

    getSpread() {
      return [
        this.container,
        ...this.elements
      ];
   }

}