import Block from "../Block.js";
import BlockBuilder from "../../BlockBuilder.js";
import {headingSelectors} from "./headingSelectors.js";

export default class HeadingBlock extends Block {
    container;

    headingTypeInput;

    valueInput;

    blockBuilder = new BlockBuilder(headingSelectors.classes.headingBlockContainer);

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateView();
    }

    generateView() {
        [this.container, this.headingTypeInput, this.valueInput] = this.blockBuilder.
            generateSelect(
                'Heading Type',
            {0: 'h1', 1: 'h2', 2: 'h3', 3: 'h4', 4: 'h5', 5: 'h6'},
            `${this.getInputName()}[headingType]`)
            .generateInput('Heading',`${this.getInputName()}[value]`)
            .getSpread();
        this.blockBuilder = null;
    }

    getInputName() {
        return `blocks[${this.getBlockId()}][heading]`;
    }

    getViewNode() {
        return this.container;
    }

    populateWithData() {
       if(this.data.value) {
           this.valueInput.value = this.data.value;
       }
       if(this.data.headingType) {
           this.headingTypeInput.value = this.data.headingType;
       }
    }


}