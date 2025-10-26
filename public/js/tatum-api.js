// Tatum API integration for cryptocurrency operations

const TATUM_API_KEY = 't-682249d000217a12f8e8139c-1c51fbe60d1b4ba6afd18bb9';
const TATUM_BASE_URL = 'https://api.tatum.io/v3';

// API client class
class TatumAPI {
    constructor(apiKey = TATUM_API_KEY) {
        this.apiKey = apiKey;
        this.baseURL = TATUM_BASE_URL;
        this.headers = {
            'Content-Type': 'application/json',
            'x-api-key': apiKey
        };
    }

    // Generic API request method
    async request(endpoint, method = 'GET', data = null) {
        try {
            const config = {
                method: method,
                headers: this.headers
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                config.body = JSON.stringify(data);
            }

            const response = await fetch(`${this.baseURL}${endpoint}`, config);
            
            if (!response.ok) {
                throw new Error(`API request failed: ${response.status} ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Tatum API request error:', error);
            throw error;
        }
    }

    // Bitcoin operations
    async getBitcoinBalance(address) {
        try {
            const response = await this.request(`/bitcoin/address/balance/${address}`);
            return {
                balance: response.incoming - response.outgoing,
                incoming: response.incoming,
                outgoing: response.outgoing
            };
        } catch (error) {
            console.error('Error getting Bitcoin balance:', error);
            return { balance: 0, incoming: 0, outgoing: 0 };
        }
    }

    async getBitcoinTransactions(address, pageSize = 50) {
        try {
            const response = await this.request(`/bitcoin/transaction/address/${address}?pageSize=${pageSize}`);
            return response;
        } catch (error) {
            console.error('Error getting Bitcoin transactions:', error);
            return [];
        }
    }

    // Ethereum operations
    async getEthereumBalance(address) {
        try {
            const response = await this.request(`/ethereum/account/balance/${address}`);
            return {
                balance: response.balance,
                currency: 'ETH'
            };
        } catch (error) {
            console.error('Error getting Ethereum balance:', error);
            return { balance: '0', currency: 'ETH' };
        }
    }

    async getEthereumTransactions(address, pageSize = 50) {
        try {
            const response = await this.request(`/ethereum/account/transaction/${address}?pageSize=${pageSize}`);
            return response;
        } catch (error) {
            console.error('Error getting Ethereum transactions:', error);
            return [];
        }
    }

    // ERC-20 Token operations (for USDT)
    async getERC20Balance(address, contractAddress) {
        try {
            const response = await this.request(`/ethereum/erc20/balance/${address}/${contractAddress}`);
            return {
                balance: response.balance,
                decimals: response.decimals
            };
        } catch (error) {
            console.error('Error getting ERC-20 balance:', error);
            return { balance: '0', decimals: 18 };
        }
    }

    // Litecoin operations
    async getLitecoinBalance(address) {
        try {
            const response = await this.request(`/litecoin/address/balance/${address}`);
            return {
                balance: response.incoming - response.outgoing,
                incoming: response.incoming,
                outgoing: response.outgoing
            };
        } catch (error) {
            console.error('Error getting Litecoin balance:', error);
            return { balance: 0, incoming: 0, outgoing: 0 };
        }
    }

    // XRP operations
    async getXrpBalance(address) {
        try {
            const response = await this.request(`/xrp/account/${address}`);
            return {
                balance: response.balance,
                sequence: response.sequence
            };
        } catch (error) {
            console.error('Error getting XRP balance:', error);
            return { balance: '0', sequence: 0 };
        }
    }

    // Dogecoin operations
    async getDogecoinBalance(address) {
        try {
            const response = await this.request(`/dogecoin/address/balance/${address}`);
            return {
                balance: response.incoming - response.outgoing,
                incoming: response.incoming,
                outgoing: response.outgoing
            };
        } catch (error) {
            console.error('Error getting Dogecoin balance:', error);
            return { balance: 0, incoming: 0, outgoing: 0 };
        }
    }

    // Tron operations
    async getTronBalance(address) {
        try {
            const response = await this.request(`/tron/account/${address}`);
            return {
                balance: response.balance,
                frozen: response.frozen
            };
        } catch (error) {
            console.error('Error getting Tron balance:', error);
            return { balance: '0', frozen: '0' };
        }
    }

    // Binance Smart Chain operations
    async getBscBalance(address) {
        try {
            const response = await this.request(`/bsc/account/balance/${address}`);
            return {
                balance: response.balance,
                currency: 'BNB'
            };
        } catch (error) {
            console.error('Error getting BSC balance:', error);
            return { balance: '0', currency: 'BNB' };
        }
    }

    // Generic balance fetcher
    async getBalance(address, currency) {
        const methods = {
            'BTC': () => this.getBitcoinBalance(address),
            'ETH': () => this.getEthereumBalance(address),
            'LTC': () => this.getLitecoinBalance(address),
            'USDT': () => this.getERC20Balance(address, '0xdAC17F958D2ee523a2206206994597C13D831ec7'),
            'XRP': () => this.getXrpBalance(address),
            'DOGE': () => this.getDogecoinBalance(address),
            'TRX': () => this.getTronBalance(address),
            'BNB': () => this.getBscBalance(address)
        };

        const method = methods[currency];
        if (method) {
            return await method();
        } else {
            throw new Error(`Unsupported currency: ${currency}`);
        }
    }

    // Get current exchange rates
    async getExchangeRates(currency = 'USD') {
        try {
            const response = await this.request(`/tatum/rate/${currency}`);
            return response;
        } catch (error) {
            console.error('Error getting exchange rates:', error);
            return {};
        }
    }

    // Get specific rate for a currency pair
    async getRate(baseCurrency, quoteCurrency = 'USD') {
        try {
            const response = await this.request(`/tatum/rate/${baseCurrency}?basePair=${quoteCurrency}`);
            return response.value || 0;
        } catch (error) {
            console.error('Error getting exchange rate:', error);
            return 0;
        }
    }

    // Transaction broadcasting (placeholder - would need proper implementation)
    async broadcastTransaction(signedTransaction, currency) {
        try {
            const endpoints = {
                'BTC': '/bitcoin/broadcast',
                'ETH': '/ethereum/broadcast',
                'LTC': '/litecoin/broadcast',
                'XRP': '/xrp/broadcast',
                'DOGE': '/dogecoin/broadcast',
                'TRX': '/tron/broadcast',
                'BNB': '/bsc/broadcast'
            };

            const endpoint = endpoints[currency];
            if (!endpoint) {
                throw new Error(`Broadcasting not supported for ${currency}`);
            }

            const response = await this.request(endpoint, 'POST', {
                txData: signedTransaction
            });

            return response;
        } catch (error) {
            console.error('Error broadcasting transaction:', error);
            throw error;
        }
    }

    // Estimate transaction fees
    async estimateFee(currency, speed = 'standard') {
        try {
            const feeEndpoints = {
                'BTC': '/bitcoin/fee',
                'ETH': '/ethereum/gas/estimate',
                'LTC': '/litecoin/fee',
                'DOGE': '/dogecoin/fee'
            };

            const endpoint = feeEndpoints[currency];
            if (!endpoint) {
                // Return default fees for unsupported currencies
                const defaultFees = {
                    'USDT': { slow: 2, standard: 5, fast: 10 },
                    'XRP': { slow: 0.1, standard: 0.2, fast: 0.5 },
                    'TRX': { slow: 1, standard: 2, fast: 5 },
                    'BNB': { slow: 0.001, standard: 0.002, fast: 0.005 }
                };
                return defaultFees[currency] || { slow: 0.001, standard: 0.002, fast: 0.005 };
            }

            const response = await this.request(endpoint);
            return response;
        } catch (error) {
            console.error('Error estimating fees:', error);
            // Return default fees on error
            return { slow: 0.001, standard: 0.002, fast: 0.005 };
        }
    }

    // Validate address format using Tatum
    async validateAddress(address, currency) {
        try {
            const response = await this.request(`/${currency.toLowerCase()}/address/validate/${address}`);
            return response.valid || false;
        } catch (error) {
            console.error('Error validating address:', error);
            // Fallback to local validation
            return validateCryptoAddress(address, currency);
        }
    }
}

// Create global instance
const tatumAPI = new TatumAPI();

// Utility functions for wallet operations
async function fetchWalletBalance(address, currency) {
    try {
        const balance = await tatumAPI.getBalance(address, currency);
        return formatBalance(balance, currency);
    } catch (error) {
        console.error(`Error fetching balance for ${currency}:`, error);
        return '0';
    }
}

async function fetchAllBalances(wallets) {
    const balancePromises = [];
    const currencies = Object.keys(wallets);

    for (const currency of currencies) {
        const wallet = wallets[currency];
        if (wallet && wallet.address) {
            balancePromises.push(
                fetchWalletBalance(wallet.address, currency)
                    .then(balance => ({ currency, balance }))
                    .catch(error => {
                        console.error(`Error fetching ${currency} balance:`, error);
                        return { currency, balance: '0' };
                    })
            );
        }
    }

    const results = await Promise.all(balancePromises);
    const balances = {};

    results.forEach(result => {
        balances[result.currency] = result.balance;
    });

    return balances;
}

function formatBalance(balanceData, currency) {
    if (typeof balanceData === 'object' && balanceData.balance !== undefined) {
        return parseFloat(balanceData.balance).toString();
    }
    return balanceData.toString();
}

async function convertToUSD(amount, currency) {
    try {
        const rate = await tatumAPI.getRate(currency, 'USD');
        return (parseFloat(amount) * rate).toFixed(2);
    } catch (error) {
        console.error('Error converting to USD:', error);
        return '0.00';
    }
}

// Price tracking utilities
async function getCryptoPrices(currencies) {
    const prices = {};
    
    for (const currency of currencies) {
        try {
            const rate = await tatumAPI.getRate(currency, 'USD');
            prices[currency] = rate;
        } catch (error) {
            console.error(`Error getting price for ${currency}:`, error);
            prices[currency] = 0;
        }
    }
    
    return prices;
}

// Transaction history utilities
async function getTransactionHistory(address, currency, limit = 20) {
    try {
        let transactions = [];
        
        switch (currency) {
            case 'BTC':
                transactions = await tatumAPI.getBitcoinTransactions(address, limit);
                break;
            case 'ETH':
                transactions = await tatumAPI.getEthereumTransactions(address, limit);
                break;
            case 'LTC':
                transactions = await tatumAPI.getLitecoinTransactions?.(address, limit) || [];
                break;
            default:
                transactions = [];
        }
        
        return formatTransactionHistory(transactions, currency);
    } catch (error) {
        console.error('Error getting transaction history:', error);
        return [];
    }
}

function formatTransactionHistory(transactions, currency) {
    if (!Array.isArray(transactions)) {
        return [];
    }
    
    return transactions.map(tx => ({
        id: tx.hash || tx.txid || generateTransactionId(),
        type: tx.type || 'unknown',
        amount: tx.value || tx.amount || '0',
        currency: currency,
        timestamp: tx.time || tx.timestamp || Date.now(),
        confirmations: tx.confirmations || 0,
        status: tx.confirmations > 0 ? 'confirmed' : 'pending',
        from: tx.from || tx.inputs?.[0]?.address || 'unknown',
        to: tx.to || tx.outputs?.[0]?.address || 'unknown'
    }));
}

// Export for global use
if (typeof window !== 'undefined') {
    window.TatumAPI = TatumAPI;
    window.tatumAPI = tatumAPI;
    window.WalletOperations = {
        fetchWalletBalance,
        fetchAllBalances,
        formatBalance,
        convertToUSD,
        getCryptoPrices,
        getTransactionHistory,
        formatTransactionHistory
    };
}

// Export for Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        TatumAPI,
        tatumAPI,
        fetchWalletBalance,
        fetchAllBalances,
        formatBalance,
        convertToUSD,
        getCryptoPrices,
        getTransactionHistory,
        formatTransactionHistory
    };
}
