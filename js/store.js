/**
 * js/store.js
 * Handles all localStorage operations (Database alternative)
 */

const STORE_KEY = 'bazrio_inventory_system';

// Default Database Schema
const defaultDB = {
    settings: {
        companyName: 'BAZRIO.COM',
        currency: '₹'
    },
    products: [], // { id, name, color, size, sleeve, costPrice, sellingPrice, minStock, openingStock }
    transactions: [], // { id, date, type('IN'|'OUT'), productId, qty, rate, total, paid, pending, entityName, remarks }
    adjustments: [] // { id, date, productId, systemQty, actualQty, difference, reason }
};

class Store {
    constructor() {
        this.init();
    }

    init() {
        const data = localStorage.getItem(STORE_KEY);
        if (!data) {
            this.saveAll(defaultDB);
        }
    }

    getAll() {
        return JSON.parse(localStorage.getItem(STORE_KEY)) || defaultDB;
    }

    saveAll(data) {
        localStorage.setItem(STORE_KEY, JSON.stringify(data));
    }

    // --- Products ---
    getProducts() {
        return this.getAll().products;
    }

    addProduct(product) {
        const data = this.getAll();
        product.id = 'P' + Date.now();
        data.products.push(product);
        this.saveAll(data);
        return product;
    }

    updateProduct(product) {
        const data = this.getAll();
        const index = data.products.findIndex(p => p.id === product.id);
        if (index !== -1) {
            data.products[index] = product;
            this.saveAll(data);
            return true;
        }
        return false;
    }

    deleteProduct(id) {
        const data = this.getAll();
        data.products = data.products.filter(p => p.id !== id);
        // Also delete related transactions
        data.transactions = data.transactions.filter(t => t.productId !== id);
        this.saveAll(data);
    }

    getProductById(id) {
        return this.getProducts().find(p => p.id === id);
    }

    // --- Transactions (Stock IN / OUT) ---
    getTransactions() {
        return this.getAll().transactions;
    }

    addTransaction(tx) {
        const data = this.getAll();
        tx.id = (tx.type === 'IN' ? 'IN' : 'OUT') + Date.now();
        data.transactions.push(tx);
        this.saveAll(data);
        return tx;
    }

    updateTransaction(tx) {
        const data = this.getAll();
        const index = data.transactions.findIndex(t => t.id === tx.id);
        if (index !== -1) {
            data.transactions[index] = tx;
            this.saveAll(data);
            return true;
        }
        return false;
    }

    deleteTransaction(id) {
        const data = this.getAll();
        data.transactions = data.transactions.filter(t => t.id !== id);
        this.saveAll(data);
    }

    getTransactionById(id) {
        return this.getTransactions().find(t => t.id === id);
    }

    // --- Inventory Calculations ---
    getProductStock(productId) {
        const p = this.getProductById(productId);
        if(!p) return 0;

        let stock = parseFloat(p.openingStock) || 0;
        const txs = this.getTransactions().filter(t => t.productId === productId);

        txs.forEach(t => {
            if(t.type === 'IN') stock += parseFloat(t.qty);
            if(t.type === 'OUT') stock -= parseFloat(t.qty);
        });

        // Note: Adjustments logic would apply here too

        return stock;
    }

    // --- System / Settings ---
    getSettings() {
        return this.getAll().settings;
    }

    saveSettings(settings) {
        const data = this.getAll();
        data.settings = settings;
        this.saveAll(data);
    }

    // --- Backup & Restore ---
    exportBackup() {
        const data = localStorage.getItem(STORE_KEY);
        const blob = new Blob([data], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Bazrio_Backup_${new Date().toISOString().slice(0,10)}.json`;
        a.click();
        URL.revokeObjectURL(url);
    }

    importBackup(jsonData) {
        try {
            const parsed = JSON.parse(jsonData);
            if(parsed.products && parsed.transactions) {
                this.saveAll(parsed);
                return true;
            }
            return false;
        } catch (e) {
            return false;
        }
    }

    clearAll() {
        if(confirm("DANGER: This will delete all data. Are you sure?")) {
            localStorage.removeItem(STORE_KEY);
            this.init();
            return true;
        }
        return false;
    }
}

// Global instance
const db = new Store();
