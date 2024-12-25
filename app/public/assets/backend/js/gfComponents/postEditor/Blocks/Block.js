import {blockSelectors} from "./blockSelectors.js";
import {blockAssets} from "./blockAssets.js";
import {blockEvents} from "./blockEvents.js";

export default class Block {
    blockContainer;

    blockActionsContainer;

    anchor;


    blockActions = new Map();

    constructor(id, blockName, handlerEventEmitter, data) {
        this.id = id;
        this.blockName = blockName;
        this.handlerEventEmitter = handlerEventEmitter;
        this.data = data;
        this.generateBlockContainer();
        this.generateBlockAnchor();
        this.generateToggleBlockActionsButton();
        this.generateBlockActionsContainer();
    }

    populateIfDataExists() {
        if(this.data) { //@todo validate by checking if data content exists
            this.populateWithData();
        }
    }

    populateWithData() {
        throw new Error('A block must implement the populateWithData method.');
    }

    getViewNode() {
        const node = document.createElement('div');
        node.innerText = this.blockName;
        return node;
    }

    getBlockNode(expand = false) {
        this.setBaseBlockActions();
        if(!expand) {
            this.getViewNode().classList.add(blockSelectors.classes.toggleVisibility);
        }
        this.blockContainer.appendChild(this.getBlockAnchor());
        this.blockContainer.appendChild(this.getViewNode());
        this.blockContainer.appendChild(this.getToggleBlockActionsButton());
        this.blockContainer.appendChild(this.getBlockActionsContainer());
        this.blockContainer.setAttribute(blockSelectors.attributes.id, this.id);
        this.populateIfDataExists();
        return this.blockContainer;
    }

    generateBlockAnchor() {
        this.anchor = document.createElement('div');
        this.anchor.classList.add(blockSelectors.classes.anchor);
        this.anchor.innerText = this.getBlockName();
        this.anchor.addEventListener('click', () => {
            this.getViewNode().classList.toggle(blockSelectors.classes.toggleVisibility);
        });
    }

    getBlockAnchor() {
        return this.anchor;
    }

    getBlockId() {
        return this.id;
    }

    getBlockName() {
        return this.blockName;
    }

    generateBlockContainer() {
        this.blockContainer = document.createElement('div');
        this.blockContainer.classList.add(blockSelectors.classes.blockContainer)
    }

    generateToggleBlockActionsButton() {
        this.toggleBlockActionsButton = document.createElement('div');
        this.toggleBlockActionsButton.classList.add(blockSelectors.classes.toggleBlockActionsButton);
        this.toggleBlockActionsButton.innerHTML = blockAssets.settingsIcon;
        this.toggleBlockActionsButton.addEventListener('click', () => {
           this.toggleBlockActionsContainerVisibility();
        });
    }

    toggleBlockActionsContainerVisibility() {
        this.getBlockActionsContainer().classList.toggle(blockSelectors.classes.show);
    }

    hideBlockActionsContainer() {
        this.getBlockActionsContainer().classList.remove(blockSelectors.classes.show);
    }

    isBlockActionsContainerOpen() {
        return this.getBlockActionsContainer().classList.contains(blockSelectors.classes.show);
    }

    generateBlockActionsContainer() {
        this.blockActionsContainer = document.createElement('div');
        this.blockActionsContainer.classList.add(blockSelectors.classes.blockActionsContainer);
    }

    getToggleBlockActionsButton() {
        return this.toggleBlockActionsButton;
    }

    getBlockContainer() {
        return this.blockContainer;
    }

    getBlockActionsContainer() {
        return this.blockActionsContainer;
    }

    getBlockActions() {
        return this.blockActions;
    }

    setBlockAction(key, value) {
        this.blockActions.set(key, value);
    }

    getBlockAction(actionName) {
        return this.blockActions.get(actionName);
    }

    addBlockAction(actionName, content, callback, className = null) {
        if(!this.getBlockAction(actionName)) {
            this.setBlockAction(actionName, this.assembleAction(content, callback, className));
            return true;
        }
        console.warn(`${actionName} block action is already registered.`);
        return false;
    }

    assembleAction(content, callback, className) {
        const container = document.createElement('div');
        container.classList.add(blockSelectors.classes.blockAction);
        if(className) {
            container.classList.add(className);
        }
        container.innerHTML = content;
        container.addEventListener('click', () => {callback()});
        this.getBlockActionsContainer().appendChild(container);
        return {
            node: container,
            content: content,
            callback: callback
        }
    }

    setBaseBlockActions() {
        this.addBlockAction('deleteBlock', `Delete ${this.blockName}`, () => {
            this.deleteBlock();
        }, blockSelectors.classes.deleteBlockAction);
    }

    deleteBlock() {
        this.getBlockContainer().remove();
        this.handlerEventEmitter.emit(blockEvents.blockDeleted, {id: this.id});
    }

    generateContainerWithLabel(label) {
        const container = document.createElement('div');
        container.classList.add(blockSelectors.classes.inputContainer);
        const labelElement = document.createElement('label');
        labelElement.innerText = label;
        container.appendChild(labelElement);
        return container;
    }

    generateInput(name) {
        const input = document.createElement('input');
        input.name = name;
        input.classList.add(blockSelectors.classes.input);
        return input;
    }

    generateTextarea(name) {
        const textarea = document.createElement('textarea');
        textarea.name = name;
        textarea.classList.add(blockSelectors.classes.input);
        return textarea;
    }
}