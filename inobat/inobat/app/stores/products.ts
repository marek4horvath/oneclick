import type { ProductPayload } from "~/interface/product"

export const useProductStore = defineStore('products', {
    state: () => {
        return {
            products: [{
            "@id": "/api/product_templates/1efabce9-300b-6a3c-9711-47916a4422c8",
            "@type": "ProductTemplate",
            "id": "1efabce9-300b-6a3c-9711-47916a4422c8",
            "name": "new test product",
            "productImage": "https://www.newzealand.com/assets/Tourism-NZ/Nelson/img-1536221079-4393-6462-6778B1E0-0D02-97B2-3D592E45C515F277__aWxvdmVrZWxseQo_CropResizeWzE5MDAsMTAwMCw3NSwianBnIl0.jpg",
            "haveDpp": false,
            "companies": [
                {
                    "@id": "/api/companies/1ef9b834-eaa9-6e1c-9244-3f3ee73f4b45",
                    "@type": "Company",
                    "name": "Test production company",
                    "latitude": 48.6543227,
                    "longitude": 21.2628516,
                    "id": "1ef9b834-eaa9-6e1c-9244-3f3ee73f4b45"
                }
            ],
            "stepsTemplate": {
                "@id": "/api/steps_templates/1efabce9-3246-61bc-bace-f3d24b9cf0c2",
                "@type": "StepsTemplate",
                "id": "1efabce9-3246-61bc-bace-f3d24b9cf0c2",
                "name": "new test product StepTemplate",
                "steps": [
                    {
                        "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                        "@type": "Step",
                        "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                        "name": "new test product ",
                        "quantity": 1,
                        "stepImage": "",
                        "batchTypeOfStep": "BATCH",
                        "process": "RAW_MATERIAL_COLLECTION",
                        "sort": 0,
                        "steps": [
                            {
                                "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                "@type": "Step",
                                "id": "1efabcea-89b6-602c-b990-599ae6054324",
                                "name": "step 2",
                                "stepImage": "",
                                "batchTypeOfStep": "BATCH",
                                "process": "PACKAGING",
                                "parentStep": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                "sort": 1,
                                "steps": [
                                    {
                                        "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                        "@type": "Step",
                                        "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                        "name": "step 3",
                                        "quantity": 1,
                                        "stepImage": "",
                                        "batchTypeOfStep": "DISCRETE_SINGLE",
                                        "process": "OEM_MANUFACTURING",
                                        "parentStep": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                        "sort": 2,
                                        "steps": [
                                            {
                                                "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                                "@type": "Step",
                                                "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                                "name": "step 4",
                                                "quantity": 2,
                                                "stepImage": "",
                                                "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                                "process": "RAW_MATERIAL_COLLECTION",
                                                "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                                "sort": 3,
                                                "steps": [],
                                                "inputs": [
                                                    {
                                                        "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                        "@type": "Input",
                                                        "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                        "type": "coordinates",
                                                        "name": "test coor",
                                                        "sort": 1
                                                    }
                                                ]
                                            }
                                        ],
                                        "inputs": [
                                            {
                                                "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                                "@type": "Input",
                                                "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                                "type": "numerical",
                                                "name": "test num",
                                                "sort": 1
                                            }
                                        ]
                                    }
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "@type": "Input",
                                        "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "type": "file",
                                        "name": "test file",
                                        "sort": 1
                                    }
                                ]
                            }
                        ],
                        "inputs": [
                            {
                                "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                "@type": "Input",
                                "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                "type": "text",
                                "name": "input text",
                                "sort": 1
                            }
                        ]
                    },
                    {
                        "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                        "@type": "Step",
                        "id": "1efabcea-89b6-602c-b990-599ae6054324",
                        "name": "step 2",
                        "stepImage": "",
                        "batchTypeOfStep": "BATCH",
                        "process": "PACKAGING",
                        "parentStep": {
                            "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                            "@type": "Step",
                            "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                            "name": "new test product ",
                            "quantity": 1,
                            "stepImage": "",
                            "batchTypeOfStep": "BATCH",
                            "process": "RAW_MATERIAL_COLLECTION",
                            "sort": 0,
                            "steps": [
                                "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                    "@type": "Input",
                                    "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                    "type": "text",
                                    "name": "input text",
                                    "sort": 1
                                }
                            ]
                        },
                        "sort": 1,
                        "steps": [
                            {
                                "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                "@type": "Step",
                                "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                "name": "step 3",
                                "quantity": 1,
                                "stepImage": "",
                                "batchTypeOfStep": "DISCRETE_SINGLE",
                                "process": "OEM_MANUFACTURING",
                                "parentStep": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                "sort": 2,
                                "steps": [
                                    {
                                        "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                        "@type": "Step",
                                        "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                        "name": "step 4",
                                        "quantity": 2,
                                        "stepImage": "",
                                        "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                        "process": "RAW_MATERIAL_COLLECTION",
                                        "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                        "sort": 3,
                                        "steps": [],
                                        "inputs": [
                                            {
                                                "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                "@type": "Input",
                                                "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                "type": "coordinates",
                                                "name": "test coor",
                                                "sort": 1
                                            }
                                        ]
                                    }
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                        "@type": "Input",
                                        "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                        "type": "numerical",
                                        "name": "test num",
                                        "sort": 1
                                    }
                                ]
                            }
                        ],
                        "inputs": [
                            {
                                "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                "@type": "Input",
                                "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                "type": "file",
                                "name": "test file",
                                "sort": 1
                            }
                        ]
                    },
                    {
                        "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                        "@type": "Step",
                        "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                        "name": "step 3",
                        "quantity": 1,
                        "stepImage": "",
                        "batchTypeOfStep": "DISCRETE_SINGLE",
                        "process": "OEM_MANUFACTURING",
                        "parentStep": {
                            "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                            "@type": "Step",
                            "id": "1efabcea-89b6-602c-b990-599ae6054324",
                            "name": "step 2",
                            "stepImage": "",
                            "batchTypeOfStep": "BATCH",
                            "process": "PACKAGING",
                            "parentStep": {
                                "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                "@type": "Step",
                                "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                                "name": "new test product ",
                                "quantity": 1,
                                "stepImage": "",
                                "batchTypeOfStep": "BATCH",
                                "process": "RAW_MATERIAL_COLLECTION",
                                "sort": 0,
                                "steps": [
                                    "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                        "@type": "Input",
                                        "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                        "type": "text",
                                        "name": "input text",
                                        "sort": 1
                                    }
                                ]
                            },
                            "sort": 1,
                            "steps": [
                                "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048"
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                    "@type": "Input",
                                    "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                    "type": "file",
                                    "name": "test file",
                                    "sort": 1
                                }
                            ]
                        },
                        "sort": 2,
                        "steps": [
                            {
                                "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                "@type": "Step",
                                "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                "name": "step 4",
                                "quantity": 2,
                                "stepImage": "",
                                "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                "process": "RAW_MATERIAL_COLLECTION",
                                "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                "sort": 3,
                                "steps": [],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                        "@type": "Input",
                                        "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                        "type": "coordinates",
                                        "name": "test coor",
                                        "sort": 1
                                    }
                                ]
                            }
                        ],
                        "inputs": [
                            {
                                "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                "@type": "Input",
                                "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                "type": "numerical",
                                "name": "test num",
                                "sort": 1
                            }
                        ]
                    },
                    {
                        "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                        "@type": "Step",
                        "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                        "name": "step 4",
                        "quantity": 2,
                        "stepImage": "",
                        "batchTypeOfStep": "DISCRETE_MULTIPLE",
                        "process": "RAW_MATERIAL_COLLECTION",
                        "parentStep": {
                            "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                            "@type": "Step",
                            "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                            "name": "step 3",
                            "quantity": 1,
                            "stepImage": "",
                            "batchTypeOfStep": "DISCRETE_SINGLE",
                            "process": "OEM_MANUFACTURING",
                            "parentStep": {
                                "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                "@type": "Step",
                                "id": "1efabcea-89b6-602c-b990-599ae6054324",
                                "name": "step 2",
                                "stepImage": "",
                                "batchTypeOfStep": "BATCH",
                                "process": "PACKAGING",
                                "parentStep": {
                                    "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                    "@type": "Step",
                                    "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                                    "name": "new test product ",
                                    "quantity": 1,
                                    "stepImage": "",
                                    "batchTypeOfStep": "BATCH",
                                    "process": "RAW_MATERIAL_COLLECTION",
                                    "sort": 0,
                                    "steps": [
                                        "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                                    ],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                            "@type": "Input",
                                            "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                            "type": "text",
                                            "name": "input text",
                                            "sort": 1
                                        }
                                    ]
                                },
                                "sort": 1,
                                "steps": [
                                    "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048"
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "@type": "Input",
                                        "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "type": "file",
                                        "name": "test file",
                                        "sort": 1
                                    }
                                ]
                            },
                            "sort": 2,
                            "steps": [
                                "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a"
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                    "@type": "Input",
                                    "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                    "type": "numerical",
                                    "name": "test num",
                                    "sort": 1
                                }
                            ]
                        },
                        "sort": 3,
                        "steps": [],
                        "inputs": [
                            {
                                "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                "@type": "Input",
                                "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                "type": "coordinates",
                                "name": "test coor",
                                "sort": 1
                            }
                        ]
                    }
                ]
            },
            "description": "",
            "nodes": [
                "/api/nodes/1efabcfb-8d38-6fea-a7da-713435d3b70b"
            ]
            }] as unknown as ProductPayload[],
        }
    },

    getters: {
        getProducts: ({ products }: { products: ProductPayload[]; }) => products,

        getProductById: ({ products }: { products: ProductPayload[]; }) =>
            (id: string) => products.find((product) => product.id === id),

        getProductsByCompanyId: ({ products }: { products: ProductPayload[]; }) =>
            (id: string) =>
                products.filter(
                    (product) =>
                        Array.isArray(product.companies) &&
                        product.companies.some((company) => company.id === id)
                ),
    },

    actions: {
        addProduct(payload: ProductPayload) {
            this.products.push(payload)
        },

        resetToDefault() {
            this.products = [{
                "@id": "/api/product_templates/1efabce9-300b-6a3c-9711-47916a4422c8",
                "@type": "ProductTemplate",
                "id": "1efabce9-300b-6a3c-9711-47916a4422c8",
                "name": "new test product ",
                "productImage": "1efabce9-300b-6a3c-9711-47916a4422c8-67458397e157c325756506.jpeg",
                "haveDpp": false,
                "companies": [
                    {
                        "@id": "/api/companies/1ef9b834-eaa9-6e1c-9244-3f3ee73f4b45",
                        "@type": "Company",
                        "name": "Test production company",
                        "latitude": 48.6543227,
                        "longitude": 21.2628516,
                        "id": "1ef9b834-eaa9-6e1c-9244-3f3ee73f4b45"
                    }
                ],
                "stepsTemplate": {
                    "@id": "/api/steps_templates/1efabce9-3246-61bc-bace-f3d24b9cf0c2",
                    "@type": "StepsTemplate",
                    "id": "1efabce9-3246-61bc-bace-f3d24b9cf0c2",
                    "name": "new test product StepTemplate",
                    "steps": [
                        {
                            "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                            "@type": "Step",
                            "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                            "name": "new test product ",
                            "quantity": 1,
                            "stepImage": "",
                            "batchTypeOfStep": "BATCH",
                            "process": "RAW_MATERIAL_COLLECTION",
                            "sort": 0,
                            "steps": [
                                {
                                    "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                    "@type": "Step",
                                    "id": "1efabcea-89b6-602c-b990-599ae6054324",
                                    "name": "step 2",
                                    "stepImage": "",
                                    "batchTypeOfStep": "BATCH",
                                    "process": "PACKAGING",
                                    "parentStep": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                    "sort": 1,
                                    "steps": [
                                        {
                                            "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                            "@type": "Step",
                                            "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                            "name": "step 3",
                                            "quantity": 1,
                                            "stepImage": "",
                                            "batchTypeOfStep": "DISCRETE_SINGLE",
                                            "process": "OEM_MANUFACTURING",
                                            "parentStep": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                            "sort": 2,
                                            "steps": [
                                                {
                                                    "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                                    "@type": "Step",
                                                    "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                                    "name": "step 4",
                                                    "quantity": 2,
                                                    "stepImage": "",
                                                    "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                                    "process": "RAW_MATERIAL_COLLECTION",
                                                    "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                                    "sort": 3,
                                                    "steps": [],
                                                    "inputs": [
                                                        {
                                                            "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                            "@type": "Input",
                                                            "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                            "type": "coordinates",
                                                            "name": "test coor",
                                                            "sort": 1
                                                        }
                                                    ]
                                                }
                                            ],
                                            "inputs": [
                                                {
                                                    "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                                    "@type": "Input",
                                                    "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                                    "type": "numerical",
                                                    "name": "test num",
                                                    "sort": 1
                                                }
                                            ]
                                        }
                                    ],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                            "@type": "Input",
                                            "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                            "type": "file",
                                            "name": "test file",
                                            "sort": 1
                                        }
                                    ]
                                }
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                    "@type": "Input",
                                    "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                    "type": "text",
                                    "name": "input text",
                                    "sort": 1
                                }
                            ]
                        },
                        {
                            "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                            "@type": "Step",
                            "id": "1efabcea-89b6-602c-b990-599ae6054324",
                            "name": "step 2",
                            "stepImage": "",
                            "batchTypeOfStep": "BATCH",
                            "process": "PACKAGING",
                            "parentStep": {
                                "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                "@type": "Step",
                                "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                                "name": "new test product ",
                                "quantity": 1,
                                "stepImage": "",
                                "batchTypeOfStep": "BATCH",
                                "process": "RAW_MATERIAL_COLLECTION",
                                "sort": 0,
                                "steps": [
                                    "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                        "@type": "Input",
                                        "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                        "type": "text",
                                        "name": "input text",
                                        "sort": 1
                                    }
                                ]
                            },
                            "sort": 1,
                            "steps": [
                                {
                                    "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                    "@type": "Step",
                                    "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                    "name": "step 3",
                                    "quantity": 1,
                                    "stepImage": "",
                                    "batchTypeOfStep": "DISCRETE_SINGLE",
                                    "process": "OEM_MANUFACTURING",
                                    "parentStep": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                    "sort": 2,
                                    "steps": [
                                        {
                                            "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                            "@type": "Step",
                                            "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                            "name": "step 4",
                                            "quantity": 2,
                                            "stepImage": "",
                                            "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                            "process": "RAW_MATERIAL_COLLECTION",
                                            "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                            "sort": 3,
                                            "steps": [],
                                            "inputs": [
                                                {
                                                    "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                    "@type": "Input",
                                                    "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                                    "type": "coordinates",
                                                    "name": "test coor",
                                                    "sort": 1
                                                }
                                            ]
                                        }
                                    ],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                            "@type": "Input",
                                            "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                            "type": "numerical",
                                            "name": "test num",
                                            "sort": 1
                                        }
                                    ]
                                }
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                    "@type": "Input",
                                    "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                    "type": "file",
                                    "name": "test file",
                                    "sort": 1
                                }
                            ]
                        },
                        {
                            "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                            "@type": "Step",
                            "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                            "name": "step 3",
                            "quantity": 1,
                            "stepImage": "",
                            "batchTypeOfStep": "DISCRETE_SINGLE",
                            "process": "OEM_MANUFACTURING",
                            "parentStep": {
                                "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                "@type": "Step",
                                "id": "1efabcea-89b6-602c-b990-599ae6054324",
                                "name": "step 2",
                                "stepImage": "",
                                "batchTypeOfStep": "BATCH",
                                "process": "PACKAGING",
                                "parentStep": {
                                    "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                    "@type": "Step",
                                    "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                                    "name": "new test product ",
                                    "quantity": 1,
                                    "stepImage": "",
                                    "batchTypeOfStep": "BATCH",
                                    "process": "RAW_MATERIAL_COLLECTION",
                                    "sort": 0,
                                    "steps": [
                                        "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                                    ],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                            "@type": "Input",
                                            "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                            "type": "text",
                                            "name": "input text",
                                            "sort": 1
                                        }
                                    ]
                                },
                                "sort": 1,
                                "steps": [
                                    "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048"
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "@type": "Input",
                                        "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                        "type": "file",
                                        "name": "test file",
                                        "sort": 1
                                    }
                                ]
                            },
                            "sort": 2,
                            "steps": [
                                {
                                    "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                    "@type": "Step",
                                    "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                                    "name": "step 4",
                                    "quantity": 2,
                                    "stepImage": "",
                                    "batchTypeOfStep": "DISCRETE_MULTIPLE",
                                    "process": "RAW_MATERIAL_COLLECTION",
                                    "parentStep": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                    "sort": 3,
                                    "steps": [],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                            "@type": "Input",
                                            "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                            "type": "coordinates",
                                            "name": "test coor",
                                            "sort": 1
                                        }
                                    ]
                                }
                            ],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                    "@type": "Input",
                                    "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                    "type": "numerical",
                                    "name": "test num",
                                    "sort": 1
                                }
                            ]
                        },
                        {
                            "@id": "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                            "@type": "Step",
                            "id": "1efabcec-fa16-6f86-97b6-e9ebcaa6751a",
                            "name": "step 4",
                            "quantity": 2,
                            "stepImage": "",
                            "batchTypeOfStep": "DISCRETE_MULTIPLE",
                            "process": "RAW_MATERIAL_COLLECTION",
                            "parentStep": {
                                "@id": "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                "@type": "Step",
                                "id": "1efabceb-ed29-6cca-b1c2-d391fbadb048",
                                "name": "step 3",
                                "quantity": 1,
                                "stepImage": "",
                                "batchTypeOfStep": "DISCRETE_SINGLE",
                                "process": "OEM_MANUFACTURING",
                                "parentStep": {
                                    "@id": "/api/steps/1efabcea-89b6-602c-b990-599ae6054324",
                                    "@type": "Step",
                                    "id": "1efabcea-89b6-602c-b990-599ae6054324",
                                    "name": "step 2",
                                    "stepImage": "",
                                    "batchTypeOfStep": "BATCH",
                                    "process": "PACKAGING",
                                    "parentStep": {
                                        "@id": "/api/steps/1efabce9-3682-6c58-a37d-dd506a5661a8",
                                        "@type": "Step",
                                        "id": "1efabce9-3682-6c58-a37d-dd506a5661a8",
                                        "name": "new test product ",
                                        "quantity": 1,
                                        "stepImage": "",
                                        "batchTypeOfStep": "BATCH",
                                        "process": "RAW_MATERIAL_COLLECTION",
                                        "sort": 0,
                                        "steps": [
                                            "/api/steps/1efabcea-89b6-602c-b990-599ae6054324"
                                        ],
                                        "inputs": [
                                            {
                                                "@id": "/api/inputs/1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                                "@type": "Input",
                                                "id": "1efabce9-cff1-6858-98b7-331aaa6ccb94",
                                                "type": "text",
                                                "name": "input text",
                                                "sort": 1
                                            }
                                        ]
                                    },
                                    "sort": 1,
                                    "steps": [
                                        "/api/steps/1efabceb-ed29-6cca-b1c2-d391fbadb048"
                                    ],
                                    "inputs": [
                                        {
                                            "@id": "/api/inputs/1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                            "@type": "Input",
                                            "id": "1efabceb-2369-65b6-b345-b5a4bcd3249a",
                                            "type": "file",
                                            "name": "test file",
                                            "sort": 1
                                        }
                                    ]
                                },
                                "sort": 2,
                                "steps": [
                                    "/api/steps/1efabcec-fa16-6f86-97b6-e9ebcaa6751a"
                                ],
                                "inputs": [
                                    {
                                        "@id": "/api/inputs/1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                        "@type": "Input",
                                        "id": "1efabcec-5c39-6f02-a607-81fd44bb6d7d",
                                        "type": "numerical",
                                        "name": "test num",
                                        "sort": 1
                                    }
                                ]
                            },
                            "sort": 3,
                            "steps": [],
                            "inputs": [
                                {
                                    "@id": "/api/inputs/1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                    "@type": "Input",
                                    "id": "1efabcef-2958-6af4-9f8c-a3a495b6f63a",
                                    "type": "coordinates",
                                    "name": "test coor",
                                    "sort": 1
                                }
                            ]
                        }
                    ]
                },
                "description": "",
                "nodes": [
                    "/api/nodes/1efabcfb-8d38-6fea-a7da-713435d3b70b"
                ]
            }]
        },

        deleteProduct(productId: string): void {
            const index = this.products.findIndex(product => product.id === productId);

            if (index !== -1) {
                this.products.splice(index, 1);
            }
        },

        editProduct(productId: string, updatedProduct: Partial<ProductPayload>) {
            const product = this.products.find(product => product.id === productId);

            if (product) {
                Object.assign(product, updatedProduct);
            }
        },

        deleteStep(productId: string, stepId: string) {
            const product = this.getProductById(productId);
            if (product && product.stepsTemplate) {
                const index = product.stepsTemplate.steps?.findIndex(step => step.id === stepId);

                if (index !== -1) {
                    product.stepsTemplate.steps?.splice(index, 1);
                }
            }
        },

        editStep(productId: string, stepId: string, updatedStep: Partial<any>) {
            const product = this.products.find(product => product.id === productId);
            if (product?.stepsTemplate?.steps) {
                const step = product.stepsTemplate.steps.find(step => step.id === stepId);

                if (step) {
                    Object.assign(step, updatedStep);
                }
            }
        },

        deleteInput(productId: string, stepId: string, inputId: string) {
            const product = this.products.find(p => p.id === productId);
            if (product?.stepsTemplate?.steps) {
                const stepIndex = product.stepsTemplate.steps.findIndex(s => s.id === stepId);
                if (stepIndex !== -1 && product.stepsTemplate.steps[stepIndex].inputs) {
                    product.stepsTemplate.steps[stepIndex].inputs = product.stepsTemplate.steps[stepIndex].inputs.filter(i => i.id !== inputId);
                }
            }
        },

        editInput(productId: string, stepId: string, inputId: string, updatedInput: Partial<any>) {
            const product = this.getProductById(productId);

            if (!product?.stepsTemplate?.steps) {
                return;
            }

            const step = product.stepsTemplate.steps.find((step) => step.id === stepId);

            if (!step?.inputs) {
                return;
            }

            const inputIndex = step.inputs.findIndex((input) => input.id === inputId);

            if (inputIndex === -1) {
                return;
            }

            step.inputs[inputIndex] = { ...step.inputs[inputIndex], ...updatedInput };
        },

        linkProductToCompany(productId: string, companyId: string) {
            const companyStore = useCompanyStore()
            const productIndex = this.products.findIndex(p => p.id === productId);

            if (productIndex === -1) {
                return;
            }

            const company = companyStore.companies.find(c => c.id === companyId);

            if (!company) {
                return;
            }

            this.products[productIndex].companies = [
                ...(this.products[productIndex].companies ?? []),
                {
                    "@id": `/api/companies/${companyId}`,
                    "@type": "Company",
                    "name": company.name,
                    "latitude": company.latitude,
                    "longitude": company.longitude,
                    "id": companyId
                }
            ];
        }
    },

    persist: true,
})
