declare module '*.css';

export {};

declare global {
    interface Window {
        dataLayer: unknown[];
    }
}
