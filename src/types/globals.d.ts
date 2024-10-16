
interface Window {
    dayjs: typeof import('dayjs');
}

// O, si prefieres definir Moment como un tipo específico
interface Moment {
    format(formatString?: string): string;
    // Agrega otros métodos y propiedades según necesites
}