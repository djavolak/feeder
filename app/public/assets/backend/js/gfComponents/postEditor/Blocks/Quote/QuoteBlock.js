import Block from "../Block.js";
import {quoteSelectors} from "./quoteSelectors.js";
import {blockSelectors} from "../blockSelectors.js";
import BlockBuilder from "../../BlockBuilder.js";

export default class QuoteBlock extends Block {
    inputName;

    textInput;

    signatureInput;

    blockBuilder = new BlockBuilder(quoteSelectors.classes.quoteBlockContainer);

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateView();
    }


    generateView() {
        [this.container, this.textInput, this.signatureInput] =
            this.blockBuilder
                .generateTextarea('Text', this.getTextInputName())
                .generateInput('Signature', this.getSignatureInputName())
                .getSpread();
        this.blockBuilder = null;
    }

    getTextInputName() {
       return `blocks[${this.getBlockId()}][quote][text]`;
    }

    getSignatureInputName() {
        return `blocks[${this.getBlockId()}][quote][signature]`;
    }

    getTextInput() {
        return this.textInput;
    }

    getInputName() {
        return this.inputName;
    }

    getSignatureInput() {
        return this.signatureInput;
    }

    getViewNode() {
        return this.container;
    }

    populateWithData() {
        if(this.data) {
          if(this.data.text && this.data.text.trim() !== '') {
              this.getTextInput().value = this.data.text;
          }
            if(this.data.signature && this.data.signature.trim() !== '') {
                this.getSignatureInput().value = this.data.signature;
            }
        }
    }
}