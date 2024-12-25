import BlockHandler from "../BlockHandler.js";
import {imageAssets} from "./imageAssets.js";
import ImageBlock from "./ImageBlock.js";

export default class ImageHandler extends BlockHandler {
    blockName = 'Image';
    constructor() {
        super();
    }

    getBlockSvg() {
        return imageAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new ImageBlock(id, this.blockName, this.eventEmitter, data);
    }
}