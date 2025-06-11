export function formattedDate(date?: string): string {
    if (!date) {
        return '----'
    }

    const newDate = new Date(date)

    return newDate.toLocaleString('en-GB', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
}
