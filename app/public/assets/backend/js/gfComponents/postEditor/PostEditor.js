import NewBlockButton from "./NewBlockButton/NewBlockButton.js";
import BlockHandlerCollection from "./Blocks/BlockHandlerCollection.js";
import {postEditorSelectors} from "./postEditorSelectors.js";
import {blockEvents} from "./Blocks/blockEvents.js";
import {blockSelectors} from "./Blocks/blockSelectors.js";
import TextEditorBlock from "./Blocks/TextEditor/TextEditorBlock.js";

export default class PostEditor {
    constructor(container, excludedBlocks = [], blockConfig = {}) {
        this.validateConstructor(container);
        this.container = container;
        this.excludedBlocks = excludedBlocks;
        this.blockConfig = blockConfig;
        this.blocksContainer = this.generateBlockContainer();
        this.blockHandlerCollection = new BlockHandlerCollection(this.blockConfig);
        this.newBlockButton = new NewBlockButton(this.container);
        this.nextAvailableBlockId = 0;
        this.activeBlocks = new Map();
    }

    init() {
        this.appendBlockContainer();
        this.getNewBlockButton().make();
        this.initBlockHandlers();
        this.addOnDocumentClickCloseBlockActions();
    }

    getExcludedBlocks() {
        return this.excludedBlocks;
    }

    removeBlockHandler(handlerName) {
        this.blockHandlerCollection.removeBlockHandler(handlerName);
    }

    addBlockHandler(handlerName, handler) {
        this.blockHandlerCollection.addBlockHandler(handlerName, handler);
    }

    appendBlockContainer() {
        this.getContainer().appendChild(this.getBlocksContainer());
    }

    generateBlockContainer() {
       if(!this.getBlocksContainer()) {
           const blocksContainer =  document.createElement('div');
           blocksContainer.classList.add(postEditorSelectors.classes.blocksContainer);
           return blocksContainer;
       }
       throw new Error('Block container has already been generated');
    }

    initBlockHandlers() {
        Object.values(this.getBlockHandlerCollection().getBlockHandlers()).forEach((blockHandler) => {
            if(this.getExcludedBlocks().includes(blockHandler.blockName)) {
                return;
            }
            this.populateNewBlockButtonWithBlockIcon(blockHandler);
            this.addBlockDeletedListener(blockHandler);
        });
    }

    populateNewBlockButtonWithBlockIcon(blockHandler) {
        const blockIconElement = blockHandler.getBlockIconElement();
        blockIconElement.addEventListener('click', () => {
            this.renderBlock(blockHandler);
            this.getNewBlockButton().toggleAddNewButtonVisibility();
        });
        this.getNewBlockButton().addBlockButtonToList(blockIconElement);
    }

    addBlockDeletedListener(blockHandler) {
        blockHandler.getEventEmitter().on(blockEvents.blockDeleted, (data) => {
           this.removeActiveBlock(data.id);
        });
    }

    renderBlock(blockHandler, data = null) {
        let expand = true;
        const blockId = this.getNextAvailableBlockId();
        const block = blockHandler.getBlock(blockId, data);
        if(block instanceof TextEditorBlock) {
            setTimeout(() => {block.getContentElement().focus()},0);
        }
        this.setActiveBlock(blockId, block);
        this.bumpNextAvailableBlockId();
        if(data) {
            expand = false;
        }
        this.getBlocksContainer().appendChild(block.getBlockNode(expand));
    }

    preloadBlocks(data) {
        if(data) {
            data.forEach((dataObject) => {
                if(dataObject.type) {
                    const blockHandler = this.getBlockHandlerCollection().getBlockHandler(dataObject.type + 'Handler');
                    this.renderBlock(blockHandler, dataObject);
                }
            });
        }
    }

    getNextAvailableBlockId() {
        return this.nextAvailableBlockId;
    }

    bumpNextAvailableBlockId() {
        this.nextAvailableBlockId++;
    }

    getBlocksContainer() {
        return this.blocksContainer;
    }

    getNewBlockButton() {
        return this.newBlockButton;
    }

    getContainer() {
        return this.container;
    }

    getBlockHandlerCollection() {
        return this.blockHandlerCollection;
    }

    getActiveBlocks() {
        return this.activeBlocks;
    }

    getActiveBlockById(id) {
        return this.activeBlocks.get(id);
    }

    setActiveBlock(id, block) {
        if(!this.activeBlocks.get(id)) {
            this.activeBlocks.set(id, block);
            return true;
        }
        console.warn(`Trying to set an active block which is already active. Block id: ${id}; Block name: ${block.getBlockName()}`);
    }

    removeActiveBlock(id) {
        return this.activeBlocks.delete(id);
    }

    validateConstructor(container) {
        if(!container) {
            throw new Error('Container passed to the PostEditor constructor was not found.');
        }
    }

    addOnDocumentClickCloseBlockActions() {
        document.addEventListener('click', (e) =>  {
           this.getActiveBlocks().forEach((activeBlock) => {
              if(activeBlock.isBlockActionsContainerOpen()
                  && e.target !== activeBlock.getToggleBlockActionsButton()
                  && !activeBlock.getBlockActionsContainer().contains(e.target)
              ) {
                  activeBlock.hideBlockActionsContainer();
              }
           });
        });
    }
}