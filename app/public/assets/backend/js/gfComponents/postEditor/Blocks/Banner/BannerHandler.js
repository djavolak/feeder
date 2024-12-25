import BlockHandler from "../BlockHandler.js";
import {bannerAssets} from "./bannerAssets.js";
import BannerBlock from "./BannerBlock.js";

export default class BannerHandler extends BlockHandler {
    blockName = 'Banner';
    constructor() {
        super();
    }

    getBlockSvg() {
        return bannerAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new BannerBlock(id, this.blockName, this.eventEmitter, data);
    }
}