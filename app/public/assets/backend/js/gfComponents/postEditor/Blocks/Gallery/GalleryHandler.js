import BlockHandler from "../BlockHandler.js";
import GalleryBlock from "./GalleryBlock.js";
import {galleryAssets} from "./galleryAssets.js";

export default class GalleryHandler extends BlockHandler {
    blockName = 'Gallery';

    constructor() {
        super();
    }

    getBlockSvg() {
        return galleryAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new GalleryBlock(id, this.blockName, this.eventEmitter, data);
    }
}