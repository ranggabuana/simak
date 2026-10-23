/**
 * SIMAK Modern DataTable Engine (Client-Side & Server-Side AJAX)
 * Features: Instant Search, Dynamic Column Sorting, Server-Side Pagination
 * Compatible with Alpine.js, Tailwind CSS & Laravel Blade
 */

class SimakDataTable {
    constructor(table, options = {}) {
        this.table = table;
        this.tbody = table.querySelector('tbody');
        if (!this.tbody) return;

        // Check if server-side mode is requested
        this.isServerSide = this.table.dataset.serverSide === 'true' || Boolean(this.table.dataset.url);
        this.apiUrl = this.table.dataset.url || window.location.pathname;

        this.originalRows = Array.from(this.tbody.querySelectorAll('tr:not(.datatable-empty)'));
        this.currentRows = [...this.originalRows];
        
        // Options
        const defaultPageSize = parseInt(table.dataset.pageSize || options.pageSize || 10, 10);
        this.pageSize = defaultPageSize;
        this.currentPage = parseInt(table.dataset.currentPage || 1, 10);
        this.sortColumn = -1;
        this.sortColumnName = '';
        this.sortDirection = 'asc'; // 'asc' or 'desc'
        this.searchQuery = '';
        this.searchDebounceTimer = null;
        this.isLoading = false;

        this.totalCount = parseInt(table.dataset.total || this.originalRows.length, 10);
        this.filteredCount = this.totalCount;

        this.wrapper = null;
        this.topControls = null;
        this.bottomControls = null;
        this.searchInput = null;
        this.pageSizeSelect = null;
        this.infoText = null;
        this.paginationContainer = null;
        this.loadingOverlay = null;

        this.init();
    }

    init() {
        this.buildWrapperAndControls();
        this.setupSorting();

        if (this.isServerSide) {
            // Initial server-side setup: if initial rows were already rendered in Blade, set up initial pagination
            const totalPages = Math.ceil(this.totalCount / (this.pageSize === -1 ? (this.totalCount || 1) : this.pageSize));
            const from = this.totalCount > 0 ? (this.currentPage - 1) * this.pageSize + 1 : 0;
            const to = Math.min(this.currentPage * this.pageSize, this.totalCount);
            this.updateInfo(from, to, this.filteredCount, this.totalCount);
            this.renderPagination(totalPages, this.currentPage);
        } else {
            this.updateClientSide();
        }
    }

    buildWrapperAndControls() {
        if (this.table.dataset.dtInitialized === 'true') return;
        this.table.dataset.dtInitialized = 'true';

        const parent = this.table.parentElement;

        // Top Toolbar
        this.topControls = document.createElement('div');
        this.topControls.className = 'flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs mb-3';

        // Left: Page Size Selector
        const leftBox = document.createElement('div');
        leftBox.className = 'flex items-center gap-2 text-slate-500 font-medium';
        leftBox.innerHTML = `
            <span>Tampilkan</span>
            <select class="dt-page-size px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="10" ${this.pageSize === 10 ? 'selected' : ''}>10</option>
                <option value="20" ${this.pageSize === 20 ? 'selected' : ''}>20</option>
                <option value="25" ${this.pageSize === 25 ? 'selected' : ''}>25</option>
                <option value="50" ${this.pageSize === 50 ? 'selected' : ''}>50</option>
                <option value="100" ${this.pageSize === 100 ? 'selected' : ''}>100</option>
                ${!this.isServerSide ? `<option value="-1" ${this.pageSize === -1 ? 'selected' : ''}>Semua</option>` : ''}
            </select>
            <span>entri</span>
        `;
        this.pageSizeSelect = leftBox.querySelector('select');
        this.pageSizeSelect.addEventListener('change', (e) => {
            this.pageSize = parseInt(e.target.value, 10);
            this.currentPage = 1;
            if (this.isServerSide) {
                this.fetchServerData();
            } else {
                this.updateClientSide();
            }
        });

        // Right: Search Input Box
        const rightBox = document.createElement('div');
        rightBox.className = 'relative w-full sm:w-64';
        rightBox.innerHTML = `
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input type="text" placeholder="Cari di tabel..." class="dt-search-input w-full pl-9 pr-8 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <button type="button" class="dt-clear-search absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 hidden text-sm font-bold">&times;</button>
        `;
        this.searchInput = rightBox.querySelector('input');
        const clearBtn = rightBox.querySelector('button');

        this.searchInput.addEventListener('input', (e) => {
            this.searchQuery = e.target.value.trim();
            if (this.searchQuery) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
            this.currentPage = 1;

            if (this.isServerSide) {
                clearTimeout(this.searchDebounceTimer);
                this.searchDebounceTimer = setTimeout(() => {
                    this.fetchServerData();
                }, 350);
            } else {
                this.updateClientSide();
            }
        });

        clearBtn.addEventListener('click', () => {
            this.searchInput.value = '';
            this.searchQuery = '';
            clearBtn.classList.add('hidden');
            this.currentPage = 1;
            if (this.isServerSide) {
                this.fetchServerData();
            } else {
                this.updateClientSide();
            }
        });

        this.topControls.appendChild(leftBox);
        this.topControls.appendChild(rightBox);

        // Bottom Controls (Info + Pagination)
        this.bottomControls = document.createElement('div');
        this.bottomControls.className = 'flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs pt-3 border-t border-slate-100 text-slate-500';

        this.infoText = document.createElement('div');
        this.infoText.className = 'font-medium';
        this.bottomControls.appendChild(this.infoText);

        this.paginationContainer = document.createElement('div');
        this.paginationContainer.className = 'flex items-center gap-1.5 shrink-0 flex-wrap';
        this.bottomControls.appendChild(this.paginationContainer);

        // Insert controls into DOM
        const targetElement = parent && parent.classList && parent.classList.contains('overflow-x-auto') 
            ? parent 
            : this.table;

        targetElement.parentElement.insertBefore(this.topControls, targetElement);
        if (targetElement.nextSibling) {
            targetElement.parentElement.insertBefore(this.bottomControls, targetElement.nextSibling);
        } else {
            targetElement.parentElement.appendChild(this.bottomControls);
        }
    }

    setupSorting() {
        const thead = this.table.querySelector('thead');
        if (!thead) return;

        const ths = thead.querySelectorAll('th');
        ths.forEach((th, colIndex) => {
            const text = th.innerText.trim();
            if (th.dataset.sortable === 'false' || text === 'Aksi' || text === '') {
                return;
            }

            th.classList.add('cursor-pointer', 'select-none', 'transition-colors', 'hover:bg-slate-200/70', 'group');
            
            const iconWrapper = document.createElement('span');
            iconWrapper.className = 'inline-flex items-center ml-1.5 text-slate-300 group-hover:text-slate-500 transition-colors dt-sort-icon';
            iconWrapper.innerHTML = `
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            `;
            th.appendChild(iconWrapper);

            th.addEventListener('click', () => {
                if (this.sortColumn === colIndex) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = colIndex;
                    this.sortColumnName = th.dataset.column || '';
                    this.sortDirection = 'asc';
                }

                // Update visual header icons
                ths.forEach((otherTh, otherIdx) => {
                    const icon = otherTh.querySelector('.dt-sort-icon');
                    if (!icon) return;
                    if (otherIdx === colIndex) {
                        icon.classList.remove('text-slate-300');
                        icon.classList.add('text-blue-600', 'font-bold');
                        if (this.sortDirection === 'asc') {
                            icon.innerHTML = `
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                </svg>
                            `;
                        } else {
                            icon.innerHTML = `
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            `;
                        }
                    } else {
                        icon.classList.remove('text-blue-600', 'font-bold');
                        icon.classList.add('text-slate-300');
                        icon.innerHTML = `
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        `;
                    }
                });

                if (this.isServerSide) {
                    this.currentPage = 1;
                    this.fetchServerData();
                } else {
                    this.sortRowsClientSide();
                    this.updateClientSide();
                }
            });
        });
    }

    // ==================== SERVER-SIDE AJAX METHODS ====================

    async fetchServerData() {
        if (this.isLoading) return;
        this.isLoading = true;

        // Dim table opacity while fetching
        if (this.tbody) {
            this.tbody.style.opacity = '0.5';
            this.tbody.style.pointerEvents = 'none';
        }

        try {
            // Build query params
            const params = new URLSearchParams(window.location.search);
            params.set('serverside', '1');
            params.set('page', String(this.currentPage));
            params.set('per_page', String(this.pageSize));
            if (this.searchQuery) {
                params.set('search', this.searchQuery);
            } else {
                params.delete('search');
            }

            if (this.sortColumnName) {
                params.set('sort_by', this.sortColumnName);
                params.set('sort_dir', this.sortDirection);
            }

            const url = `${this.apiUrl}?${params.toString()}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Server returned HTTP ${response.status}`);
            }

            const data = await response.json();

            // Update Table Body with Server-Rendered HTML
            if (data.html !== undefined) {
                this.tbody.innerHTML = data.html;
            }

            this.totalCount = data.total ?? 0;
            this.filteredCount = data.filtered ?? this.totalCount;
            this.currentPage = data.current_page ?? 1;

            const from = data.from ?? (this.totalCount > 0 ? (this.currentPage - 1) * this.pageSize + 1 : 0);
            const to = data.to ?? Math.min(this.currentPage * this.pageSize, this.totalCount);
            const lastPage = data.last_page ?? Math.ceil(this.filteredCount / this.pageSize);

            this.updateInfo(from, to, this.filteredCount, this.totalCount);
            this.renderPagination(lastPage, this.currentPage);

            // Re-initialize Alpine.js bindings on new DOM nodes
            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                window.Alpine.initTree(this.tbody);
            }

        } catch (err) {
            console.error('SimakDataTable server-side fetch error:', err);
            const cols = this.table.querySelectorAll('thead th').length || 1;
            this.tbody.innerHTML = `
                <tr>
                    <td colspan="${cols}" class="py-8 text-center text-rose-500 font-semibold text-xs">
                        Gagal memuat data dari server. Silakan muat ulang halaman.
                    </td>
                </tr>
            `;
        } finally {
            this.isLoading = false;
            if (this.tbody) {
                this.tbody.style.opacity = '1';
                this.tbody.style.pointerEvents = '';
            }
        }
    }

    // ==================== CLIENT-SIDE FALLBACK METHODS ====================

    parseValue(cell) {
        if (!cell) return '';
        let val = cell.innerText.trim();
        const cleanVal = val.replace(/Rp\s?|[.,%]|(?:\s?Th)|(?:\s?Jiwa)/gi, '').trim();
        if (cleanVal !== '' && !isNaN(Number(cleanVal))) {
            const normalized = val.replace(/,/g, '.').replace(/[^0-9.-]/g, '');
            const parsedNum = parseFloat(normalized);
            if (!isNaN(parsedNum)) {
                return parsedNum;
            }
        }
        return val.toLowerCase();
    }

    sortRowsClientSide() {
        if (this.sortColumn < 0) return;

        this.currentRows.sort((rowA, rowB) => {
            const cellA = rowA.children[this.sortColumn];
            const cellB = rowB.children[this.sortColumn];
            const valA = this.parseValue(cellA);
            const valB = this.parseValue(cellB);

            if (typeof valA === 'number' && typeof valB === 'number') {
                return this.sortDirection === 'asc' ? valA - valB : valB - valA;
            }

            const strA = String(valA);
            const strB = String(valB);
            const cmp = strA.localeCompare(strB, 'id', { numeric: true, sensitivity: 'base' });
            return this.sortDirection === 'asc' ? cmp : -cmp;
        });

        this.currentRows.forEach(row => this.tbody.appendChild(row));
    }

    updateClientSide() {
        let matchedRows = [];
        if (!this.searchQuery) {
            matchedRows = [...this.currentRows];
        } else {
            const q = this.searchQuery.toLowerCase();
            matchedRows = this.currentRows.filter(row => {
                const text = row.innerText.toLowerCase();
                return text.includes(q);
            });
        }

        const totalMatched = matchedRows.length;
        const totalOriginal = this.originalRows.length;

        const oldEmpty = this.tbody.querySelector('.datatable-empty');
        if (oldEmpty) oldEmpty.remove();

        if (totalMatched === 0) {
            this.currentRows.forEach(row => row.style.display = 'none');
            const cols = this.table.querySelectorAll('thead th').length || 1;
            const emptyTr = document.createElement('tr');
            emptyTr.className = 'datatable-empty';
            emptyTr.innerHTML = `
                <td colspan="${cols}" class="py-8 text-center text-slate-400 text-xs">
                    Tidak ditemukan data yang sesuai dengan pencarian "<span class="font-bold text-slate-600">${this.searchQuery}</span>"
                </td>
            `;
            this.tbody.appendChild(emptyTr);

            this.updateInfo(0, 0, 0, totalOriginal);
            this.renderPagination(0, 0);
            return;
        }

        const pageSize = this.pageSize === -1 ? totalMatched : this.pageSize;
        const totalPages = Math.ceil(totalMatched / pageSize);

        if (this.currentPage > totalPages) {
            this.currentPage = totalPages || 1;
        }

        const startIndex = (this.currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalMatched);

        this.originalRows.forEach(row => row.style.display = 'none');
        matchedRows.slice(startIndex, endIndex).forEach(row => {
            row.style.display = '';
        });

        this.updateInfo(startIndex + 1, endIndex, totalMatched, totalOriginal);
        this.renderPagination(totalPages, this.currentPage);
    }

    // ==================== SHARED UI HELPERS ====================

    updateInfo(from, to, filtered, total) {
        if (!this.infoText) return;
        if (this.searchQuery && filtered !== total) {
            this.infoText.innerHTML = `Menampilkan <strong>${from}</strong> sampai <strong>${to}</strong> dari <strong>${filtered.toLocaleString()}</strong> data <span class="text-slate-400">(difilter dari ${total.toLocaleString()} total data)</span>`;
        } else {
            this.infoText.innerHTML = `Menampilkan <strong>${from}</strong> sampai <strong>${to}</strong> dari <strong>${total.toLocaleString()}</strong> data`;
        }
    }

    renderPagination(totalPages, activePage) {
        this.paginationContainer.innerHTML = '';
        if (totalPages <= 1) return;

        // Previous Button
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = `px-3 py-1.5 rounded-xl border text-xs font-semibold transition ${
            activePage === 1 
                ? 'text-slate-300 border-slate-200 cursor-not-allowed bg-slate-50' 
                : 'text-slate-700 border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 cursor-pointer'
        }`;
        prevBtn.innerHTML = '&larr; Sebelumnya';
        if (activePage > 1) {
            prevBtn.addEventListener('click', () => {
                this.currentPage--;
                if (this.isServerSide) {
                    this.fetchServerData();
                } else {
                    this.updateClientSide();
                }
            });
        }
        this.paginationContainer.appendChild(prevBtn);

        // Page Number Buttons with dynamic windowing
        let startPage = Math.max(1, activePage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            const firstBtn = this.createPageBtn(1, activePage);
            this.paginationContainer.appendChild(firstBtn);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-1.5 text-slate-400 font-bold';
                ellipsis.innerText = '...';
                this.paginationContainer.appendChild(ellipsis);
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            const pageBtn = this.createPageBtn(p, activePage);
            this.paginationContainer.appendChild(pageBtn);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-1.5 text-slate-400 font-bold';
                ellipsis.innerText = '...';
                this.paginationContainer.appendChild(ellipsis);
            }
            const lastBtn = this.createPageBtn(totalPages, activePage);
            this.paginationContainer.appendChild(lastBtn);
        }

        // Next Button
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = `px-3 py-1.5 rounded-xl border text-xs font-semibold transition ${
            activePage === totalPages 
                ? 'text-slate-300 border-slate-200 cursor-not-allowed bg-slate-50' 
                : 'text-slate-700 border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 cursor-pointer'
        }`;
        nextBtn.innerHTML = 'Selanjutnya &rarr;';
        if (activePage < totalPages) {
            nextBtn.addEventListener('click', () => {
                this.currentPage++;
                if (this.isServerSide) {
                    this.fetchServerData();
                } else {
                    this.updateClientSide();
                }
            });
        }
        this.paginationContainer.appendChild(nextBtn);
    }

    createPageBtn(pageNum, activePage) {
        const btn = document.createElement('button');
        btn.type = 'button';
        const isActive = pageNum === activePage;
        btn.className = `w-8 h-8 rounded-xl text-xs font-bold transition flex items-center justify-center cursor-pointer ${
            isActive 
                ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/25 border border-blue-600' 
                : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'
        }`;
        btn.innerText = pageNum;
        if (!isActive) {
            btn.addEventListener('click', () => {
                this.currentPage = pageNum;
                if (this.isServerSide) {
                    this.fetchServerData();
                } else {
                    this.updateClientSide();
                }
            });
        }
        return btn;
    }
}

// Global Auto-Initialization
function initSimakDataTables() {
    const tables = document.querySelectorAll('table.datatable, table[data-datatable]');
    tables.forEach(table => {
        if (!table.dataset.dtInitialized) {
            new SimakDataTable(table);
        }
    });
}

// Auto run on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSimakDataTables);
} else {
    initSimakDataTables();
}

// Run when Alpine initialized
document.addEventListener('alpine:initialized', initSimakDataTables);

// Export for manual programmatic calls
window.SimakDataTable = SimakDataTable;
window.initSimakDataTables = initSimakDataTables;

export default SimakDataTable;
