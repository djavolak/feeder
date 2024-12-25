import Block from "../Block.js";
import {embedSelectors} from "./embedSelectors.js";
import BlockBuilder from "../../BlockBuilder.js";


export default class EmbedBlock extends Block {
    input;

    inputName;

    embedPreviewContainer;

    blockBuilder = new BlockBuilder(embedSelectors.classes.embedBlockContainer);

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
    }

    generateView() {
        [this.container, this.input, this.embedPreviewContainer] =
            this.blockBuilder
                .generateTextarea(null, this.getInputName(), 'Paste Embed', 7)
                .generateBasicContainer(embedSelectors.classes.embedPreviewContainer)
                .getSpread();
        this.addInputListener();
        this.blockBuilder = null;
    }

    generateInput() {
        this.input = document.createElement('textarea');
        this.input.name = this.getInputName();
        this.input.placeholder = 'Paste embed';
        this.input.setAttribute('rows', '7');
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][embed]`;
    }

    getInputName() {
        return this.inputName;
    }

    getInput() {
        return this.input;
    }

    addInputListener() {
        this.getInput().addEventListener('input', () => {
            this.setEmbedPreview(this.getInput().value);
        });
    }

    getEmbedPreviewContainer() {
        return this.embedPreviewContainer;
    }

    setEmbedPreview(embed) {
        this.getEmbedPreviewContainer().innerHTML = embed;
    }

    getViewNode() {
        return this.container;
    }

    populateWithData() {
        if(this.data && this.data.value) {
            this.getInput().value = this.data.value;
            this.getEmbedPreviewContainer().innerHTML = this.data.value;
        }
    }
}