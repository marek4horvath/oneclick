import type { Product } from "./api/product"

export interface ProductsState {
    products: Product[]
    totalItems: number
}
