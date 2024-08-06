/** @type {import('tailwindcss').Config} */
module.exports = {
    // prefix: 'tw-',
    content: [
        './templates/dashboard.php',
    ],
    theme: {
        extend: {
            animation: {
                'spin-reverse': 'spin-reverse 1s linear infinite',
            },
            keyframes: {
                'spin-reverse': {
                    '0%': {transform: 'rotate(360deg)'},
                    '100%': {transform: 'rotate(0deg)'},
                },
            },
            colors: {
                grayCust: {
                    100: '#E5E7EB',
                    200: '#D4D4D8',
                    300: '#6B7280',
                    600: '#1F2937',
                    350: '#D1D5DB',
                    400: '#F9FAFB',
                    500: '#D93F21',
                    850: '#343541',
                    900: '#4B5563'
                },
                primary: {
                    600: '#D1FAE5',
                    700: '#11BF85',
                    800: '#0B6C63',
                    900: '#005E54',
                    950: '#00231F'
                },
                redCust: {
                    50: '#FEE2E2',
                    100: '#FB7185'
                },
                purpleCust: {
                    50: '#DBEAFE'
                },
                yellowCust: {
                    50: '#FEF3C7',
                    100: '#F3E98D',
                }
            }
        },
    },
    plugins: [],
}

