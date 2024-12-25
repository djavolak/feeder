import Block from "../Block.js";
import {personListSelectors} from "./personListSelectors.js";
import {blockSelectors} from "../blockSelectors.js";
import Person from "./Person.js";
import {ImagePreviewInForm} from "../../../imagePreviewInForm/ImagePreviewInForm.js";
import BlockBuilder from "../../BlockBuilder.js";

export default class PersonListBlock extends Block {
    titleInput;

    descriptionInput;

    personCount = 0;

    addButtonContainer;

    addButton;

    personsContainer;

    persons = new Map();

    blockBuilder = new BlockBuilder(personListSelectors.classes.personListBlockContainer);

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][personList]`;
    }

    getInputName() {
        return this.inputName;
    }

    generateView() {
        [
            this.container,
            this.titleInput,
            this.descriptionInput,
            this.viewTypeSelect,
            this.addButton,
            this.personsContainer
        ] =
            this.blockBuilder
                .generateInput('Title', this.getTitleInputName())
                .generateTextarea('Description', this.getDescriptionInputName())
                .generateSelect('View Type', {1: 'Slider', 2: 'Grid'},this.getViewTypeSelectName())
                .generateButtonWithContainer(
                    personListSelectors.classes.addButtonContainer,
                    blockSelectors.classes.button,
                    'Add Person'
                )
                .generateBasicContainer(personListSelectors.classes.personsContainer)
                .getSpread();
        this.blockBuilder = null;
        this.addButtonListener();
    }


    getTitleInput() {
        return this.titleInput;
    }

    getTitleInputName() {
        return this.getInputName() + '[title]';
    }

    getDescriptionInput() {
        return this.descriptionInput;
    }

    getDescriptionInputName() {
        return this.getInputName() + '[description]'
    }

    getViewTypeSelectName() {
        return this.getInputName() + '[viewType]'
    }

    getPersonCount() {
        return this.personCount;
    }

    incrementPersonCount() {
        this.personCount++;
    }

    getPersonsContainer() {
        return this.personsContainer;
    }

    addButtonListener() {
        this.getAddButton().addEventListener('click', () => {
            this.generatePerson();
        });
    }

    generatePerson(data = null) {
        const id = this.getPersonCount();
        const person = new Person(id, this.getBlockId(), data, {
            generateInput: this.generateInput,
            generateTextarea: this.generateTextarea,
            generateContainerWithLabel: this.generateContainerWithLabel,
            inputName: this.getInputName() + '[persons]'
        });
        this.persons.set(id, person);
        this.incrementPersonCount();
        this.getPersonsContainer().appendChild(person.getView());
    }

    getPerson(id) {
        return this.persons.get(id);
    }

    getAddButton() {
        return this.addButton;
    }

    getViewNode() {
        return this.container;
    }

    getLinkToInput() {
        return this.linkToInput;
    }

    getViewTypeSelect() {
        return this.viewTypeSelect;
    }

    populateWithData() {
        if(this.data) {
            if(this.data.title && this.data.title.trim() !== '') {
                this.getTitleInput().value = this.data.title;
            }
            if(this.data.description && this.data.description.trim() !== '') {
                this.getDescriptionInput().value = this.data.description;
            }
            if(this.data.viewType) {
                this.getViewTypeSelect().value = this.data.viewType;
            }
            if(this.data.persons && this.data.persons.length > 0) {
                this.data.persons.forEach((personData) => {
                    this.generatePerson(personData);
                });
            }
        }
    }

    handleImageSelection(initiatorElement, media) {
        if(initiatorElement && (initiatorElement.personId || initiatorElement.personId === 0)) {
            const person = this.getPerson(initiatorElement.personId);
            if(person && media && media[0]) {
                const mediaData = media[0];
                person.addPreviewAndImageInput(ImagePreviewInForm.generate(
                    person.getImageInputName(),
                    mediaData.id,
                    `/images${mediaData.filename}`
                ));
            }
        }
    }
}