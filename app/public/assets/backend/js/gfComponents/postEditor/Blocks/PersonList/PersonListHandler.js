import BlockHandler from "../BlockHandler.js";
import PersonListBlock from "./PersonListBlock.js";
import {personListAssets} from "./personListAssets.js";

export default class PersonListHandler extends BlockHandler {
    blockName = 'Person List';

    constructor() {
        super();
    }

    getBlockSvg() {
        return personListAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new PersonListBlock(id, this.blockName, this.eventEmitter, data);
    }
}