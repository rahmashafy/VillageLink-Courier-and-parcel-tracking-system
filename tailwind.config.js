import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                vl: {
                    dark: '#172228',
                    teal: '#2E464A',
                    cream: '#F2E8DE',
                    peach: '#C89872',
                    copper: '#7F604C',
                    accent: '#E55634',
                },
            },
        },
    },

    plugins: [forms],
};
