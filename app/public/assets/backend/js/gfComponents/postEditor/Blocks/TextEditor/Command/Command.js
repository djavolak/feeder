import {commandAssets} from "./commandAssets.js";
import {commandSelectors} from "./commandSelectors.js";
import {commandEvents} from "./commandEvents.js";

export default class Command {
    constructor(commandName, eventEmitter, textEditorIndex) {
        this.commandName = commandName;
        this.eventEmitter = eventEmitter;
        this.textEditorIndex = textEditorIndex;
        this.nonTogglableCommands = ['createLink','unlink'];
    }

    generateView() {
        this.button = document.createElement('button');
        this.button.innerHTML = commandAssets.icons[this.commandName];
        this.button.title = this.commandName;
        this.addCommandListener();
        return this.button;
    }

    addCommandListener() {
        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            this.execCommand();
        });

        this.button.addEventListener('mousedown', (e) => {
           e.preventDefault();
        });
    }

    execCommand() {
        let value = null;
        this.eventEmitter.emit(commandEvents.commandClicked, this);
        if (this.commandName === 'createLink') {
            let selection = false;
            if(confirm('Should the link be opened in a new tab?')) {
                selection = document.getSelection();
            }
            let link = prompt('Enter a URL:', '');
            document.execCommand(this.commandName, false, link);
            if(selection) {
                selection.anchorNode.parentElement.target = '_blank';
            }
            return;
        }
        if(!this.nonTogglableCommands.includes(this.commandName)) {
            this.button.classList.toggle(commandSelectors.classes.active);
        }
        document.execCommand(this.commandName, false, value);
    }

    toggleActiveBasedOnCommandState(isActive) {
        isActive ? this.highlight() : this.unHighlight();
    }

    highlight() {
        this.button.classList.add(commandSelectors.classes.active);
    }

    unHighlight() {
        this.button.classList.remove(commandSelectors.classes.active);
    }

    getCommandName() {
        return this.commandName;
    }
}