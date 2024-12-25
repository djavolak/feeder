import BlockHandler from "../BlockHandler.js";
import {embedAssets} from "./embedAssets.js";
import EmbedBlock from "./EmbedBlock.js";

export default class EmbedHandler extends BlockHandler {
    blockName = 'Embed'

    constructor() {
        super();
    }

    getBlockSvg() {
        return embedAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new EmbedBlock(id, this.blockName, this.eventEmitter, data);
    }
}