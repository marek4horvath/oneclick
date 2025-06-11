export function formatText(text: string) {
    const formattedText = text.toLowerCase().replace(/_/g, ' ')

    return formattedText.charAt(0).toUpperCase() + formattedText.slice(1)
}

export function formatTextArray(texts: string[]) {
    return texts.map(text => {
        const formattedText = text.toLowerCase().replace(/_/g, ' ')

        return formattedText.charAt(0).toUpperCase() + formattedText.slice(1)
    })
}

export function revertFormattedText(text: string) {
    return text.toUpperCase().replace(/ /g, '_')
}

export function formatPascalCaseToLabel(text: string): string {
    return text
        .replace(/([A-Z])/g, ' $1')
        .toLowerCase()
        .replace(/^./, char => char.toUpperCase())
}
