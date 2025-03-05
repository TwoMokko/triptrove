import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.tsx',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dark: 'var(--dark-color)',
                main: 'var(--bg-main-color)',
                primary: 'var(--primary-color)',
                secondary: 'var(--secondary-color)',
                secondaryLight: 'var(--secondary-light-color)',
            },
        },
    },
    plugins: [],
};
