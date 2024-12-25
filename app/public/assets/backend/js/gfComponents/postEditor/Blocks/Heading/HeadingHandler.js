import BlockHandler from "../BlockHandler.js";
import HeadingBlock from "./HeadingBlock.js";
import {headingAssets} from "./headingAssets.js";

export default class HeadingHandler extends BlockHandler {
    blockName = 'Heading'

    constructor() {
        super();
    }

    getBlockSvg() {
        return headingAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new HeadingBlock(id, this.blockName, this.eventEmitter, data);
    }
}