export interface HydraCollection<T> {
    '@context': string
    '@id': string
    '@type': string
    'hydra:totalItems': number
    'hydra:member': T[]
    'hydra:search': object | null
    'hydra:view': {
        'hydra:first': string | null
        'hydra:last': string | null
        'hydra:next': string | null
        'hydra:previous': string | null
    } | null
}
