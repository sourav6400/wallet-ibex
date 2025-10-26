// Cryptocurrency utilities and wallet generation

// BIP39 word list (simplified - first 100 words for demo)
const BIP39_WORDLIST = [
    'abandon', 'ability', 'able', 'about', 'above', 'absent', 'absorb', 'abstract', 'absurd', 'abuse',
    'access', 'accident', 'account', 'accuse', 'achieve', 'acid', 'acoustic', 'acquire', 'across', 'act',
    'action', 'actor', 'actress', 'actual', 'adapt', 'add', 'addict', 'address', 'adjust', 'admit',
    'adult', 'advance', 'advice', 'aerobic', 'affair', 'afford', 'afraid', 'again', 'age', 'agent',
    'agree', 'ahead', 'aim', 'air', 'airport', 'aisle', 'alarm', 'album', 'alcohol', 'alert',
    'alien', 'all', 'alley', 'allow', 'almost', 'alone', 'alpha', 'already', 'also', 'alter',
    'always', 'amateur', 'amazing', 'among', 'amount', 'amused', 'analyst', 'anchor', 'ancient', 'anger',
    'angle', 'angry', 'animal', 'ankle', 'announce', 'annual', 'another', 'answer', 'antenna', 'antique',
    'anxiety', 'any', 'apart', 'apology', 'appear', 'apple', 'approve', 'april', 'arch', 'arctic',
    'area', 'arena', 'argue', 'arm', 'armed', 'armor', 'army', 'around', 'arrange', 'arrest',
    'arrive', 'arrow', 'art', 'article', 'artist', 'artwork', 'ask', 'aspect', 'assault', 'asset',
    'assist', 'assume', 'asthma', 'athlete', 'atom', 'attack', 'attend', 'attitude', 'attract', 'auction'
];

// Extended word list (additional words to reach 120 for better demo)
const EXTENDED_WORDLIST = [
    ...BIP39_WORDLIST,
    'audit', 'august', 'aunt', 'author', 'auto', 'autumn', 'average', 'avocado', 'avoid', 'awake',
    'aware', 'away', 'awesome', 'awful', 'awkward', 'axis', 'baby', 'bachelor', 'bacon', 'badge'
];

// Generate a random mnemonic phrase
function generateMnemonic() {
    const words = [];
    for (let i = 0; i < 12; i++) {
        const randomIndex = Math.floor(Math.random() * EXTENDED_WORDLIST.length);
        words.push(EXTENDED_WORDLIST[randomIndex]);
    }
    return words.join(' ');
}

// Validate mnemonic phrase
function validateMnemonic(mnemonic) {
    if (!mnemonic || typeof mnemonic !== 'string') {
        return false;
    }
    
    const words = mnemonic.trim().toLowerCase().split(/\s+/);
    
    // Check if we have exactly 12 words
    if (words.length !== 12) {
        return false;
    }
    
    // Check if all words are in the word list
    return words.every(word => EXTENDED_WORDLIST.includes(word));
}

// Generate a pseudo-random hash from seed phrase
function hashFromSeed(seed, index = 0) {
    let hash = 0;
    const input = seed + index.toString();
    
    for (let i = 0; i < input.length; i++) {
        const char = input.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32-bit integer
    }
    
    return Math.abs(hash).toString(36);
}

// Generate a mock private key from seed
function generatePrivateKey(seed, currency, index = 0) {
    const hash = hashFromSeed(seed + currency, index);
    // Generate a 64-character hex string for private key
    let privateKey = '';
    for (let i = 0; i < 64; i++) {
        privateKey += Math.floor(Math.random() * 16).toString(16);
    }
    return privateKey;
}

// Generate a mock public key from private key
function generatePublicKey(privateKey) {
    // Simple hash of private key for demo purposes
    let hash = 0;
    for (let i = 0; i < privateKey.length; i++) {
        hash = ((hash << 5) - hash) + privateKey.charCodeAt(i);
        hash = hash & hash;
    }
    return Math.abs(hash).toString(16).padStart(64, '0');
}

// Generate address from public key for different currencies
function generateAddress(publicKey, currency) {
    const addresses = {
        'BTC': () => {
            // Generate a mock Bitcoin address
            const prefixes = ['1', '3', 'bc1'];
            const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
            const hash = hashFromSeed(publicKey, 1);
            if (prefix === 'bc1') {
                return `bc1q${hash.substr(0, 39)}`;
            } else {
                return `${prefix}${hash.substr(0, 33)}`;
            }
        },
        'ETH': () => {
            // Generate a mock Ethereum address
            const hash = hashFromSeed(publicKey, 2);
            return `0x${hash.substr(0, 40)}`;
        },
        'LTC': () => {
            // Generate a mock Litecoin address
            const prefixes = ['L', 'M', '3', 'ltc1'];
            const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
            const hash = hashFromSeed(publicKey, 3);
            if (prefix === 'ltc1') {
                return `ltc1q${hash.substr(0, 39)}`;
            } else {
                return `${prefix}${hash.substr(0, 33)}`;
            }
        },
        'USDT': () => {
            // USDT uses Ethereum addresses
            const hash = hashFromSeed(publicKey, 4);
            return `0x${hash.substr(0, 40)}`;
        },
        'XRP': () => {
            // Generate a mock Ripple address
            const hash = hashFromSeed(publicKey, 5);
            return `r${hash.substr(0, 33)}`;
        },
        'DOGE': () => {
            // Generate a mock Dogecoin address
            const hash = hashFromSeed(publicKey, 6);
            return `D${hash.substr(0, 33)}`;
        },
        'TRX': () => {
            // Generate a mock Tron address
            const hash = hashFromSeed(publicKey, 7);
            return `T${hash.substr(0, 33)}`;
        },
        'BNB': () => {
            // Generate a mock Binance address
            const hash = hashFromSeed(publicKey, 8);
            return `bnb${hash.substr(0, 39)}`;
        }
    };
    
    const generator = addresses[currency];
    return generator ? generator() : addresses['BTC']();
}

// Generate wallets for all supported currencies from seed phrase
async function generateWalletsFromSeed(seedPhrase) {
    const wallets = {};
    const supportedCurrencies = ['BTC', 'ETH', 'LTC', 'USDT', 'XRP', 'DOGE', 'TRX', 'BNB'];
    
    for (const currency of supportedCurrencies) {
        try {
            const privateKey = generatePrivateKey(seedPhrase, currency);
            const publicKey = generatePublicKey(privateKey);
            const address = generateAddress(publicKey, currency);
            
            wallets[currency] = {
                address: address,
                publicKey: publicKey,
                privateKey: privateKey,
                balance: '0',
                transactions: [],
                createdAt: new Date().toISOString()
            };
        } catch (error) {
            console.error(`Error generating wallet for ${currency}:`, error);
            // Set a default wallet structure
            wallets[currency] = {
                address: 'Error generating address',
                publicKey: '',
                privateKey: '',
                balance: '0',
                transactions: [],
                createdAt: new Date().toISOString()
            };
        }
    }
    
    return wallets;
}

// Get currency network information
function getCurrencyNetwork(currency) {
    const networks = {
        'BTC': {
            name: 'Bitcoin',
            symbol: 'BTC',
            decimals: 8,
            network: 'bitcoin',
            confirmations: 6,
            fees: {
                slow: 0.0001,
                standard: 0.0002,
                fast: 0.0005
            }
        },
        'ETH': {
            name: 'Ethereum',
            symbol: 'ETH',
            decimals: 18,
            network: 'ethereum',
            confirmations: 12,
            fees: {
                slow: 0.001,
                standard: 0.002,
                fast: 0.005
            }
        },
        'LTC': {
            name: 'Litecoin',
            symbol: 'LTC',
            decimals: 8,
            network: 'litecoin',
            confirmations: 6,
            fees: {
                slow: 0.001,
                standard: 0.002,
                fast: 0.005
            }
        },
        'USDT': {
            name: 'Tether',
            symbol: 'USDT',
            decimals: 6,
            network: 'ethereum',
            confirmations: 12,
            fees: {
                slow: 1,
                standard: 2,
                fast: 5
            }
        },
        'XRP': {
            name: 'Ripple',
            symbol: 'XRP',
            decimals: 6,
            network: 'ripple',
            confirmations: 1,
            fees: {
                slow: 0.1,
                standard: 0.2,
                fast: 0.5
            }
        },
        'DOGE': {
            name: 'Dogecoin',
            symbol: 'DOGE',
            decimals: 8,
            network: 'dogecoin',
            confirmations: 6,
            fees: {
                slow: 1,
                standard: 2,
                fast: 5
            }
        },
        'TRX': {
            name: 'Tron',
            symbol: 'TRX',
            decimals: 6,
            network: 'tron',
            confirmations: 19,
            fees: {
                slow: 1,
                standard: 2,
                fast: 5
            }
        },
        'BNB': {
            name: 'Binance Coin',
            symbol: 'BNB',
            decimals: 18,
            network: 'binance-smart-chain',
            confirmations: 15,
            fees: {
                slow: 0.001,
                standard: 0.002,
                fast: 0.005
            }
        }
    };
    
    return networks[currency] || networks['BTC'];
}

// Format amount based on currency decimals
function formatCryptoAmount(amount, currency) {
    const network = getCurrencyNetwork(currency);
    const decimals = Math.min(network.decimals, 8); // Limit display decimals
    return parseFloat(amount).toFixed(decimals);
}

// Convert between different units
function convertUnits(amount, fromUnit, toUnit, currency) {
    const network = getCurrencyNetwork(currency);
    const decimals = network.decimals;
    
    // For simplicity, we'll just handle basic conversion
    if (fromUnit === 'base' && toUnit === 'display') {
        return amount / Math.pow(10, decimals);
    } else if (fromUnit === 'display' && toUnit === 'base') {
        return amount * Math.pow(10, decimals);
    }
    
    return amount;
}

// Generate a transaction ID
function generateTransactionId() {
    const chars = '0123456789abcdef';
    let txId = '';
    for (let i = 0; i < 64; i++) {
        txId += chars[Math.floor(Math.random() * chars.length)];
    }
    return txId;
}

// Create a mock transaction
function createTransaction(from, to, amount, currency, fee, type = 'send') {
    return {
        id: generateTransactionId(),
        from: from,
        to: to,
        amount: amount,
        currency: currency,
        fee: fee,
        type: type,
        status: 'pending',
        confirmations: 0,
        timestamp: new Date().toISOString(),
        block: null,
        blockHeight: null
    };
}

// Validate cryptocurrency address format
function validateCryptoAddress(address, currency) {
    const patterns = {
        'BTC': /^([13][a-km-zA-HJ-NP-Z1-9]{25,34}|bc1[a-z0-9]{39,59})$/,
        'ETH': /^0x[a-fA-F0-9]{40}$/,
        'LTC': /^([LM3][a-km-zA-HJ-NP-Z1-9]{26,33}|ltc1[a-z0-9]{39,59})$/,
        'USDT': /^0x[a-fA-F0-9]{40}$/,
        'XRP': /^r[0-9a-zA-Z]{24,34}$/,
        'DOGE': /^D{1}[5-9A-HJ-NP-U]{1}[1-9A-HJ-NP-Za-km-z]{32}$/,
        'TRX': /^T[A-Za-z1-9]{33}$/,
        'BNB': /^bnb[0-9a-z]{39}$/
    };
    
    const pattern = patterns[currency];
    return pattern ? pattern.test(address) : false;
}

// Simple key derivation (for demo purposes only)
function deriveKey(seed, path) {
    // This is a very simplified key derivation for demo purposes
    // In production, use proper BIP32/BIP44 implementation
    let hash = 0;
    const input = seed + path;
    
    for (let i = 0; i < input.length; i++) {
        const char = input.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    
    return Math.abs(hash).toString(16).padStart(64, '0');
}

// Export functions
if (typeof window !== 'undefined') {
    window.CryptoUtils = {
        generateMnemonic,
        validateMnemonic,
        generateWalletsFromSeed,
        getCurrencyNetwork,
        formatCryptoAmount,
        convertUnits,
        generateTransactionId,
        createTransaction,
        validateCryptoAddress,
        deriveKey,
        hashFromSeed,
        generatePrivateKey,
        generatePublicKey,
        generateAddress
    };
}

// Export for Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        generateMnemonic,
        validateMnemonic,
        generateWalletsFromSeed,
        getCurrencyNetwork,
        formatCryptoAmount,
        convertUnits,
        generateTransactionId,
        createTransaction,
        validateCryptoAddress,
        deriveKey,
        hashFromSeed,
        generatePrivateKey,
        generatePublicKey,
        generateAddress
    };
}
