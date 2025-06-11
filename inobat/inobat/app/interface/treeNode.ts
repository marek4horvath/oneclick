export interface TreeNode {
    id: number
    name: string
    parent: string
    img: string
    ord: string
    inputs: Array<{ id: number; name: string; img: string }>
    children?: TreeNode[]
}
