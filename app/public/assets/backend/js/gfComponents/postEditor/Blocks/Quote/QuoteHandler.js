import BlockHandler from "../BlockHandler.js";
import QuoteBlock from "./QuoteBlock.js";
import {quoteAssets} from "./quoteAssets.js";

export default class QuoteHandler extends BlockHandler {
    blockName = 'Quote'
    constructor() {
        super();
    }

    getBlockSvg() {
        return quoteAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new QuoteBlock(id, this.blockName, this.eventEmitter, data);
    }
}