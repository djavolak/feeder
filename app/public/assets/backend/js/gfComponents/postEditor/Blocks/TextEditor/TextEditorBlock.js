import Block from "../Block.js";
import {textEditorSelectors} from "./textEditorSelectors.js";
import EventEmitter from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/EventEmitter/EventEmitter.js";
import {commandSelectors} from "./Command/commandSelectors.js";
import Command from "./Command/Command.js";
import {commandEvents} from "./Command/commandEvents.js";

export default class TextEditorBlock extends Block {
    container;

    contentElement;

    inputName;

    commandNames =
        [
            'bold',
            'createLink',
            'unlink',
            'insertOrderedList',
            'insertUnorderedList',
            'italic',
            'underline',
            'justifyLeft',
            'justifyCenter',
            'justifyRight'
        ];

    commands = new Map();

    eventEmitter = new EventEmitter();

    currentActiveElement = null;

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
        this.mount();
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][textEditor]`;
    }

    getInputName() {
        return this.inputName;
    }

    generateView() {
       this.generateContentContainer();
       this.generateCommandsContainer();
    }

    populateWithData() {
        this.setContent(this.data.value);
    }

    setContent(content) {
        this.getContentElement().innerHTML = content;
        this.getInput().value = content;
    }

    generateContentContainer() {
        this.container = document.createElement('div');
        this.container.classList.add(textEditorSelectors.classes.container);

        this.contentElement = document.createElement('div');
        this.contentElement.spellcheck = false;
        this.contentElement.classList.add(textEditorSelectors.classes.content);
        this.contentElement.contentEditable = 'true';

        this.input = document.createElement('input');
        this.input.type = 'hidden';
        this.input.name = this.getInputName();

        this.container.appendChild(this.contentElement);
        this.container.appendChild(this.input);
    }
    generateCommandsContainer() {
        this.commandsContainer = document.createElement('div');
        this.commandsContainer.classList.add(commandSelectors.classes.container);
    }

    getInput() {
        return this.input;
    }

    getContentElement() {
        return this.contentElement;
    }
    mount() {
        this.generateCommands();
        this.addListeners();
        this.listenForCommands();
    }



    generateCommands() {
        this.commandNames.forEach((commandName) => {
            const command = new Command(commandName, this.eventEmitter, this.id);
            this.commands.set(commandName, command);
            this.commandsContainer.appendChild(command.generateView());
        });
        this.container.appendChild(this.commandsContainer);
    }

    addListeners() {
        this.contentElement.addEventListener('focus', () => {this.addFocusListener()});
        this.contentElement.addEventListener('blur', () => {this.addBlurListener()});
        this.contentElement.addEventListener('input', () => {this.addChangeListener()});
        this.contentElement.addEventListener('paste', (e) => {this.addPasteListener(e)});
        ['mouseup', 'keyup'].forEach((event) => {
            this.contentElement.addEventListener(event, () => {
                this.handleCommandsBasedOnSelection();
            });
        });
        this.contentElement.addEventListener('click', (e) => {
            this.currentActiveElement = e.target;
        })
    }

    addFocusListener()  {
        this.commandsContainer.classList.add(textEditorSelectors.classes.show);
    }

    addBlurListener() {
        this.commandsContainer.classList.remove(textEditorSelectors.classes.show);
        this.commands.forEach((command) => {
            command.unHighlight();
        });
    }

    addChangeListener() {
        this.input.value = this.contentElement.innerHTML;
    }

    addPasteListener(e) {
        e.preventDefault();
        document.execCommand('insertText', false, e.clipboardData.getData('text/plain'));
    }

    handleCommandsBasedOnSelection() {
        this.commands.get('bold').toggleActiveBasedOnCommandState(document.queryCommandState('bold'));
        this.commands.get('insertOrderedList').toggleActiveBasedOnCommandState(document.queryCommandState('insertOrderedList'));
        this.commands.get('insertUnorderedList').toggleActiveBasedOnCommandState(document.queryCommandState('insertUnorderedList'));
        this.commands.get('italic').toggleActiveBasedOnCommandState(document.queryCommandState('italic'));
        this.commands.get('underline').toggleActiveBasedOnCommandState(document.queryCommandState('underline'));
        this.commands.get('justifyLeft').toggleActiveBasedOnCommandState(document.queryCommandState('justifyLeft'));
        this.commands.get('justifyCenter').toggleActiveBasedOnCommandState(document.queryCommandState('justifyCenter'));
        this.commands.get('justifyRight').toggleActiveBasedOnCommandState(document.queryCommandState('justifyRight'));
    }


    listenForCommands() {
        this.eventEmitter.on(commandEvents.commandClicked, (command) => {
            if(command.getCommandName() === 'insertOrderedList' && document.queryCommandState('insertUnorderedList')) {
                this.commands.get('insertUnorderedList').unHighlight();
            }
            if(command.getCommandName() === 'insertUnorderedList' && document.queryCommandState('insertOrderedList')) {
                this.commands.get('insertOrderedList').unHighlight();
            }
            const justifyCommands = ['justifyLeft', 'justifyCenter', 'justifyRight'];
            if(justifyCommands.includes(command.commandName)) {
                justifyCommands.forEach((commandName) => {
                    if (command.commandName !== commandName) {
                        this.commands.get(commandName).unHighlight();
                    }
                });
            }
        });
    }

    setInputName(value) {
        this.inputName = value;
    }

    getViewNode() {
        return this.container;
    }
}