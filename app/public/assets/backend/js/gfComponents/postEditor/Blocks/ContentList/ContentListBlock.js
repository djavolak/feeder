import Block from "../Block.js";
import {contentListSelectors} from "./contentListSelectors.js";
import {blockSelectors} from "../blockSelectors.js";
import Content from "./Content.js";
import BlockBuilder from "../../BlockBuilder.js";

export default class ContentListBlock extends Block {
    viewTypes = {
        1: "FAQ"
    };
    nextAvailableIndex = 0;

    blockBuilder = new BlockBuilder(contentListSelectors.classes.container);

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateView();
    }

    getInputName() {
        return `blocks[${this.getBlockId()}][contentList]`;
    }

    generateView() {
        [this.container, this.viewTypeSelect, this.addButton, this.contentContainer] =
            this.blockBuilder
                .generateSelect('View Type', this.viewTypes, this.getInputName() + '[viewType]')
                .generateButton(blockSelectors.classes.button, 'Add New')
                .generateBasicContainer(contentListSelectors.classes.contentContainer)
                .getSpread();
        this.blockBuilder = null;
        this.addListeners();
    }


    addListeners() {
        this.addButtonListener();

    }

    addButtonListener() {
        this.addButton.addEventListener('click', () => {
            this.generateContent();
        });
    }

    getNextAvailableIndex() {
        return this.nextAvailableIndex;
    }

    incrementNextAvailableIndex() {
        this.nextAvailableIndex++;
    }

    generateContent(title = null, content = null) {
        const contentEntity = new Content(
            `${this.getInputName()}[entities][${this.getNextAvailableIndex()}]`,
            title,
            content
        );
        this.incrementNextAvailableIndex();
        this.contentContainer.appendChild(contentEntity.getView());
    }

    getViewNode() {
        return this.container;
    }

    populateWithData() {
       if(this.data) {
           if(this.data.viewType) {
               this.viewTypeSelect.value = this.data.viewType;
           }
           if(this.data.entities && this.data.entities.length > 0) {
               this.data.entities.forEach((entity) => {
                  this.generateContent(entity.title, entity.content);
               });
           }
       }
    }
}