type DebounceFn<T extends any[]> = (...args: T) => void

/**
 * Дебаунс-функция для отложенного выполнения.
 * @param fn - Функция для выполнения
 * @param delay - Задержка в мс
 * @returns Дебаунсированная версия функции
 */
export function debounce<T extends any[]>(
    fn: (...args: T) => void,
    delay: number
): DebounceFn<T> {
    let timeoutId: ReturnType<typeof setTimeout>

    return (...args: T) => {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => fn(...args), delay)
    }
}
