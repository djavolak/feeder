import {modalSelectors} from "./modalSelectors.js";
import Loader from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Loader/Loader.js";
import Cell from "../Cell/Cell.js";
import {cellEvents} from "../Cell/cellEvents.js";
import ImageForm from "../Form/ImageForm.js";
import {mediaLibraryEvents} from "../mediaLibraryEvents.js";
import {formEvents} from "../Form/formEvents.js";

export default class Modal {

    initiatorElement = null;

    cells = new Map();

    selectedCells = new Map();

    loader = new Loader();

    options = null;

    page = 0;

    constructor(eventEmitter, allowDelete = false) {
        this.eventEmitter = eventEmitter;
        this.allowDelete = allowDelete;
        this.overlay = document.getElementById(modalSelectors.ids.overlay);
        this.container = document.getElementById(modalSelectors.ids.container);
        this.closeButton = document.getElementById(modalSelectors.ids.closeButton);
        this.topBar = document.getElementById(modalSelectors.ids.topBar);
        this.search = document.getElementById(modalSelectors.ids.search);
        this.dateFilter = document.getElementById(modalSelectors.ids.dateFilter);
        this.contentContainer = document.getElementById(modalSelectors.ids.contentContainer);
        this.cellsContainer = document.getElementById(modalSelectors.ids.cellsContainer);
        this.sidebar = document.getElementById(modalSelectors.ids.sidebar);
        this.insertMediaButton = document.getElementById(modalSelectors.ids.insertMediaButton);
        this.uploadMediaButton = document.getElementById(modalSelectors.ids.uploadMediaButton);
        this.mediaUploadInput = document.getElementById(modalSelectors.ids.mediaUploadInput);
        this.loadMoreButton = document.getElementById(modalSelectors.ids.loadMoreMediaButton);
    }

    getAllowDelete() {
        return this.allowDelete;
    }

    mount() {
        this.addCloseListeners();
        this.addSearchListener();
        this.addDateFilterListener();
        this.addCellToggleSelectListeners();
        this.addInsertMediaListener();
        this.addMediaUploadListener();
        this.addCellDeletedListener();
        this.addLoadMoreListener();
    }

    addCloseListeners() {
        this.closeButton.addEventListener('click', () => {
            this.close();
        })
        this.overlay.addEventListener('mousedown', (e) => {
            if(e.target === this.overlay) {
                this.close();
            }
        });
    }

    addSearchListener() {
        let timeout = null;
        this.search.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.page = 0;
                this.enableLoadMore();
                this.loadMedia();
            },300);
        });
    }

    addDateFilterListener() {
        if(this.dateFilter) {
            this.dateFilter.addEventListener('change', () => {
                this.loadMedia();
            });
        }
    }

    addInsertMediaListener() {
        this.insertMediaButton.addEventListener('click', () => {
            let media = [];
            if(this.selectedCells.size > 0) {
                this.selectedCells.forEach((selectedCell) => {
                   media.push(selectedCell.getMediaData());
                });
            }
            let eventData = {
                initiatorElement: this.getInitiatorElement(),
                media: media
            }
            this.close();
            this.eventEmitter.emit(mediaLibraryEvents.mediaReadyToInsert, eventData);
        });
    }

    addMediaUploadListener() {
        this.uploadMediaButton.addEventListener('click', () => {
           this.mediaUploadInput.click();
        });
        this.mediaUploadInput.addEventListener('change', async () => {
            let dummyCells = {};
            for(let i = 0; i < this.mediaUploadInput.files.length; i++) {
                dummyCells[i] = Cell.generateDummyCell();
                this.cellsContainer.prepend(dummyCells[i]);
            }
            for(let i = 0; i < this.mediaUploadInput.files.length; i++) {
                const data = new FormData();
                data.append('image', this.mediaUploadInput.files[i]);
                try {
                    let req = await fetch(`/image/create/`, {
                        method: 'POST',
                        body: data
                    });
                    let res = await req.json();
                    dummyCells[i].remove();
                    if(res.data && res.status) {
                        this.generateCells([res.data], true, true);
                    }
                } catch(e) {
                    dummyCells[i].remove();
                }
            }
            this.mediaUploadInput.value = null;
        });
    }
    addCellDeletedListener() {
        this.eventEmitter.on(formEvents.mediaDeleted, (data) => {
            /**
             * @TODO WARNING: CELLS IS A MAP, SO IT CAN ONLY HAVE 1 UNIQUE KEY (IN THIS CASE THE MEDIA DATA ID)
             *      THIS MAY BE A PROBLEM IF WE HAVE MORE ENTITIES (IMAGES,VIDEOS, PDFS.....) WHICH MAY CONTAIN THE SAME ID
             *          POSSIBLE FIX: SET KEY AS HASH OF ID + FILE NAME/TYPE?
             */
            let deletedCell = this.cells.get(data.id);
            deletedCell.destroy();
            this.cells.delete(data.id);
        });
    }

    addLoadMoreListener() {
        this.loadMoreButton.addEventListener('click', (e) => {
           e.preventDefault();
           this.loader.start(this.cellsContainer);
           this.getMedia().then((mediaData) => {
               this.generateCells(mediaData);
               this.loader.stop();
           });
        });
    }

    open(initiatorElement = null, options = null) {
        if(options) {
            this.options = options;
        }
        if(initiatorElement) {
            this.initiatorElement = initiatorElement;
        }
        this.overlay.classList.add(modalSelectors.classes.show);
        this.loadMedia();
        document.body.classList.add(modalSelectors.classes.bodyFrozen);
    }

    close() {
        this.initiatorElement = null;
        this.cells.clear();
        this.options = null;
        this.selectedCells.clear();
        this.overlay.classList.remove(modalSelectors.classes.show);
        this.emptyCellContainer();
        this.emptySidebar();
        this.page = 0;
        this.enableLoadMore();
        document.body.classList.remove(modalSelectors.classes.bodyFrozen);
    }

    loadMedia() {
        this.selectedCells.clear();
        this.emptyCellContainer();
        this.emptySidebar();
        this.loader.start(this.cellsContainer);
        this.getMedia().then((mediaData) => {
            this.generateCells(mediaData);
            this.loader.stop();
        })
    }

    emptyCellContainer() {
        this.cellsContainer.innerHTML = '';
    }

    emptySidebar() {
        this.sidebar.innerHTML = '';
    }

    getInitiatorElement() {
        return this.initiatorElement;
    }

    async getMedia() {
        let formData = new FormData();
        formData.append('filter[page]', (this.page++).toString());
        formData.append('offset', ((this.page > 1 ? ((this.page - 1) * 10) : 0).toString()));
        if(this.search.value.trim() !== '') {
            formData.append('search', `%${this.search.value.trim()}%`);
        }
        let req =  await fetch('/image/tableHandler/', {
            method: 'POST',
            body: formData
        });
        return this.parseTableHandlerJSONToMediaData(await req.json());
    }

    parseTableHandlerJSONToMediaData(tableHandlerJSONResponse) {
        const returnArray = [];
        if(tableHandlerJSONResponse.entities && tableHandlerJSONResponse.entities.data) {
            tableHandlerJSONResponse.entities.data.forEach((entry) => {
                returnArray.push(entry.columns);
            });
        }
        if(returnArray.length < 10) {
            this.disableLoadMore();
        }
        return returnArray;
    }

    disableLoadMore() {
        this.loadMoreButton.disabled = true;
    }

    enableLoadMore() {
        this.loadMoreButton.disabled = false;
    }

    generateCells(mediaData, prepend = false, replaceLastDummyCell = false) {
        let fragment = document.createDocumentFragment();
        mediaData.forEach((media) => {
            let cell = new Cell({
                mediaData: media,
                eventEmitter: this.eventEmitter,
            });
            /**
             * @TODO WARNING: SELECTED CELLS IS A MAP, SO IT CAN ONLY HAVE 1 UNIQUE KEY (IN THIS CASE THE MEDIA DATA ID)
             *      THIS MAY BE A PROBLEM IF WE HAVE MORE ENTITIES (IMAGES,VIDEOS, PDFS.....) WHICH MAY CONTAIN THE SAME ID
             *          POSSIBLE FIX: SET KEY AS HASH OF ID + FILE NAME/TYPE?
             */
            this.cells.set(cell.getImage().getId(), cell);
            fragment.appendChild(cell.getView());
        });
        let lastDummyCell = Cell.getLastDummyCell();
        if(!prepend) {
            this.cellsContainer.appendChild(fragment);
        } else if(replaceLastDummyCell && lastDummyCell) {
            this.cellsContainer.insertBefore(fragment, lastDummyCell.nextSibling);
        } else {
            this.cellsContainer.prepend(fragment);
        }

    }

    addCellToggleSelectListeners() {
        this.eventEmitter.on(cellEvents.selectToggled, (data) => {
            this.emptySidebar();
            if(this.options && !this.options.get('multiple')) {
                this.deselectSelectedCells();
            }
            /**
             * @TODO WARNING: SELECTED CELLS IS A MAP, SO IT CAN ONLY HAVE 1 UNIQUE KEY (IN THIS CASE THE MEDIA DATA ID)
             *      THIS MAY BE A PROBLEM IF WE HAVE MORE ENTITIES (IMAGES,VIDEOS, PDFS.....) WHICH MAY CONTAIN THE SAME ID
             *          POSSIBLE FIX: SET KEY AS HASH OF ID + FILE NAME/TYPE?
             */
            if(data.cell.isSelected()) {
                this.selectedCells.set(data.cell.getMediaData().id, data.cell);
            } else {
                this.selectedCells.delete(data.cell.getMediaData().id);
            }

            let lastSelectedCell = this.getLastSelectedCell();
            if(lastSelectedCell) {
                this.toSidebar(lastSelectedCell);
            }
            if(this.options.get('insertable') && this.selectedCells.size > 0) {
                this.showInsertMediaButton();
            } else {
                this.hideInsertMediaButton();
            }
        });
    }

    getLastSelectedCell() {
        return Array.from(this.selectedCells.values()).pop();
    }

    toSidebar(cell) {
        // @todo make a factory here for images,videos,pdfs...
        let imageForm = new ImageForm({
            cell: cell,
            eventEmitter: this.eventEmitter,
            allowDelete: this.getAllowDelete()
        });
        this.sidebar.appendChild(imageForm.getView());
    }

    deselectSelectedCells() {
       this.selectedCells.forEach((cell, key) => {
            cell.deselectWithoutEmitting();
            this.selectedCells.delete(key);
       });
    }

    showInsertMediaButton() {
        this.insertMediaButton.classList.remove(modalSelectors.classes.hidden);
    }

    hideInsertMediaButton() {
        this.insertMediaButton.classList.add(modalSelectors.classes.hidden);
    }
}