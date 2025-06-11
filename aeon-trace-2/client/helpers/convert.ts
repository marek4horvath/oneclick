/**
 * Convert base64 to blob for file upload
 *
 * @param base64Data
 * @param contentType
 * @returns Blob
 */
export function b64toBlob(base64Data: string, contentType = ''): Blob {
    const pos = base64Data.indexOf(';base64,')
    const type = contentType || base64Data.slice(5, pos)
    const b64 = base64Data.slice(pos + 8, base64Data.length)

    const byteString = atob(b64)

    const buffer = new ArrayBuffer(byteString.length)

    const view = new Uint8Array(buffer)

    for (let i = 0; i < byteString.length; i++) {
        view[i] = byteString.charCodeAt(i)
    }

    // write the ArrayBuffer to a blob, and you're done
    return new Blob([buffer], { type })
}
