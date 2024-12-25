import {newBlockButtonAssets} from "./newBlockButtonAssets.js";
import {selectors} from "./selectors.js";
import {blockSelectors} from "../Blocks/blockSelectors.js";

export default class NewBlockButton {
    constructor(mainContainer) {
        this.mainContainer = mainContainer;
        this.buttonContainer = null;
        this.addNewButton = null;
        this.blockListContainer = null;
    }

    make() {
        this.generateView();
        this.addListeners();
        this.mainContainer.appendChild(this.getButtonContainer());
    }


    generateView() {
        this.generateButtonContainer();
        this.generateAddNewButton();
        this.generateSearchInput();
        this.addSearchListener();
        this.generateBlockListContainer();
        this.merge();
    }

    generateButtonContainer() {
        this.buttonContainer = document.createElement('div');
        this.buttonContainer.classList.add(selectors.classes.buttonContainer);
    }

    generateAddNewButton() {
        this.addNewButton = document.createElement('div');
        this.addNewButton.classList.add(selectors.classes.addNewButton);
        this.addNewButton.innerHTML = newBlockButtonAssets.addNewButtonIcon;
    }

    generateBlockListContainer() {
        this.blockListContainer = document.createElement('div');
        this.blockListContainer.classList.add(selectors.classes.blockListContainer);
        this.blockListContainer.appendChild(this.getSearchInput());
    }

    getButtonContainer() {
        return this.buttonContainer;
    }

    getAddNewButton() {
        return this.addNewButton;
    }

    generateSearchInput() {
        this.searchInput = document.createElement('input');
        this.searchInput.placeholder = 'Search for blocks...';
    }

    getSearchInput() {
        return this.searchInput;
    }

    addSearchListener() {
        this.getSearchInput().addEventListener('input', () => {
            const searchValue = this.getSearchInput().value.trim();
            this.getBlockListContainer().querySelectorAll(`.${blockSelectors.classes.icon}`).forEach((icon) => {
                if(icon.getAttribute(blockSelectors.attributes.blockName).toLowerCase().includes(searchValue.toLowerCase())
                    || searchValue.length === 0) {
                    icon.classList.remove(blockSelectors.classes.hide);
                } else {
                    icon.classList.add(blockSelectors.classes.hide);
                }


            });
        });
    }

    getBlockListContainer() {
        return this.blockListContainer;
    }

    merge() {
        this.buttonContainer.appendChild(this.getAddNewButton());
        this.buttonContainer.appendChild(this.getBlockListContainer());
    }

    addListeners() {
        this.addNewButtonListener();
        this.addCloseListenerOnClickOutsideOfContainer();
    }

    addNewButtonListener() {
        this.addNewButton.addEventListener('click', () => {
           this.toggleAddNewButtonVisibility();
        });
    }

    addCloseListenerOnClickOutsideOfContainer() {
        document.addEventListener('click', (e) => {
            if(e.target !== this.buttonContainer && !this.buttonContainer.contains(e.target) && e.target !== this.addNewButton) {
                this.hide();
            }
        })
    }

    toggleAddNewButtonVisibility() {
        this.blockListContainer.classList.toggle(selectors.classes.show);
        this.addNewButton.classList.toggle(selectors.classes.active);
    }

    hide() {
        this.blockListContainer.classList.remove(selectors.classes.show);
        this.addNewButton.classList.remove(selectors.classes.active);
    }

    addBlockButtonToList(blockButton) {
        this.blockListContainer.appendChild(blockButton);
    }

}