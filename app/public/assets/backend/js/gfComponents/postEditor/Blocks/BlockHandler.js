import {blockSelectors} from "./blockSelectors.js";
import {blockAssets} from "./blockAssets.js";
import Block from "./Block.js";
import EventEmitter from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/EventEmitter/EventEmitter.js";

export default class BlockHandler {

    eventEmitter = new EventEmitter();

    blockName = 'Block';

    getBlockIconElement() {
        const container = document.createElement('div');
        container.classList.add(blockSelectors.classes.icon);
        container.innerHTML = this.getBlockSvg();
        container.setAttribute(blockSelectors.attributes.blockName, this.blockName);
        container.title = this.blockName;
        return container;
    }

    getBlockSvg() {
        return blockAssets.blockIcon;
    }

    getBlock(id, data = null) {
        return new Block(null, this.blockName, this.eventEmitter, null);
    }

    getEventEmitter() {
        return this.eventEmitter;
    }


}