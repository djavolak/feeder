import {blockAssets} from "../blockAssets.js";
import {contentListSelectors} from "./contentListSelectors.js";
import {blockSelectors} from "../blockSelectors.js";

export default class Content {
    constructor(inputName, title, content) {
        this.initialTitle = title;
        this.initialContent = content;
        this.inputName = inputName;
        this.generateView();
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(contentListSelectors.classes.contentEntityContainer);
        this.generateTitleInput();
        this.generateContentInput();
        this.generateDeleteButton();
        this.container.appendChild(this.getTitleInput());
        this.container.appendChild(this.getContentInput());
        this.container.appendChild(this.getDeleteButton());
    }

    getView() {
        return this.container;
    }

    generateTitleInput() {
        this.titleInput = document.createElement('input');
        this.titleInput.name = this.getInputName() + '[title]';
        this.titleInput.classList.add(blockSelectors.classes.input);
        this.titleInput.placeholder = 'Title';
        if(this.getInitialTitle() && this.getInitialTitle().trim() !== '') {
            this.titleInput.value = this.getInitialTitle();
        }
    }

    getTitleInput() {
        return this.titleInput;
    }

    generateContentInput() {
        this.contentInput = document.createElement('textarea');
        this.contentInput.name = this.getInputName() + '[content]';
        this.contentInput.classList.add(blockSelectors.classes.input);
        this.contentInput.placeholder = 'Content';
        if(this.getInitialContent() && this.getInitialContent().trim() !== '') {
            this.contentInput.value = this.getInitialContent();
        }
    }

    getContentInput() {
        return this.contentInput;
    }

    generateDeleteButton() {
        this.deleteButton = document.createElement('div');
        this.deleteButton.innerHTML = blockAssets.deleteIcon;
        this.deleteButton.classList.add(contentListSelectors.classes.contentEntityDeleteButton);
        this.deleteButton.title = 'Delete';
        this.deleteButton.addEventListener('click', () => {
            this.getContainer().remove();
        });
    }

    getDeleteButton() {
        return this.deleteButton;
    }

    getInitialTitle() {
        return this.initialTitle;
    }

    getInitialContent() {
        return this.initialContent;
    }

    getInputName() {
        return this.inputName;
    }

    getContainer() {
        return this.container;
    }
}