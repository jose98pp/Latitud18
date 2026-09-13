export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
        // CSS optimization plugins for production
        ...(process.env.NODE_ENV === 'production' && {
            cssnano: {
                preset: ['default', {
                    discardComments: {
                        removeAll: true,
                    },
                    normalizeWhitespace: true,
                    colormin: true,
                    convertValues: true,
                    discardDuplicates: true,
                    discardEmpty: true,
                    mergeRules: true,
                    minifyFontValues: true,
                    minifyParams: true,
                    minifySelectors: true,
                    normalizeCharset: true,
                    normalizeDisplayValues: true,
                    normalizePositions: true,
                    normalizeRepeatStyle: true,
                    normalizeString: true,
                    normalizeTimingFunctions: true,
                    normalizeUnicode: true,
                    normalizeUrl: true,
                    orderedValues: true,
                    reduceIdents: true,
                    reduceInitial: true,
                    reduceTransforms: true,
                    svgo: true,
                    uniqueSelectors: true
                }]
            }
        })
    },
};
