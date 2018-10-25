module.exports = {
    "root": true,
    "extends": "eslint:recommended",
    "globals": {
        "wp": true
    },
    "env": {
        "node": true,
        "es6": true,
        "amd": true,
        "browser": true,
        "jquery": true
    },
    "parser": 'babel-eslint',
    "parserOptions": {
        "sourceType": 'module',
        "allowImportExportEverywhere": true
    },
    "plugins": [
        "import"
    ],
    "settings": {
        "import/core-modules": [],
        "import/ignore": [
            "node_modules",
            "\\.(coffee|scss|css|less|hbs|svg|json)$"
        ]
    },
    "rules": {
        "no-console": 0,
        "import/export": "warn",
        "comma-dangle": [
            "error",
            {
                "arrays": "always-multiline",
                "objects": "always-multiline",
                "imports": "always-multiline",
                "exports": "always-multiline",
                "functions": "ignore"
            }
        ]
    }
}
