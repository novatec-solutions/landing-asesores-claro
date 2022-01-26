'use strict';

const JavaScriptObfuscator = require('webpack-obfuscator');

module.exports = {
    plugins: [
        new JavaScriptObfuscator({
            compact: true,
            controlFlowFlattening: false,
            rotateUnicodeArray: true,
            stringArray: true,
            stringArrayEncoding: true,
            stringArrayThreshold: 0.8,
            unicodeEscapeSequence: true
        }, [])
    ]
};
