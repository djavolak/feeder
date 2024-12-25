import BlockHandler from "../BlockHandler.js";
import EntityListBlock from "./EntityListBlock.js";
import {entityListAssets} from "./entityListAssets.js";

export default class EntityListHandler extends BlockHandler {
    blockName = 'Entity List';
    constructor(blockConfig = {}) {
        super();
        this.blockConfig = blockConfig;
    }

    getBlockSvg() {
        return entityListAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new EntityListBlock(id, this.blockName, this.eventEmitter, data, this.blockConfig);
    }
}