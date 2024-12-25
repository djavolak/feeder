import BlockHandler from "../BlockHandler.js";
import ContentListBlock from "./ContentListBlock.js";
import {contentListAssets} from "./contentListAssets.js";

export default class ContentListHandler extends BlockHandler {
    blockName = 'Content List';
    constructor() {
        super();
    }

    getBlockSvg() {
        return contentListAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new ContentListBlock(id, this.blockName, this.eventEmitter, data);
    }
}