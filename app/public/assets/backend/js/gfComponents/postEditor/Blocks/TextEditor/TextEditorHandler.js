import BlockHandler from "../BlockHandler.js";
import {textEditorAssets} from "./textEditorAssets.js";
import TextEditorBlock from "./TextEditorBlock.js";

export default class TextEditorHandler extends BlockHandler {
    blockName = 'Text Editor';
    constructor() {
        super();
        document.execCommand('defaultParagraphSeparator', false, 'p');
    }

    getBlockSvg() {
        return textEditorAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new TextEditorBlock(id, this.blockName, this.eventEmitter, data);
    }
}