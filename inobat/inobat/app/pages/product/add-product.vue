<template>
    <NuxtLayout>
        <div class="add-product">
            <v-form ref="form" v-model="valid" @keyup.native.enter="submit">
                <v-container>
                    <v-row no-gutters>
                        <v-col>
                            <v-text-field 
                                v-model="formProduct.name" 
                                :label="$t('products.name')" 
                                type="text" 
                                required
                                :rules="nameRules"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col>
                            <v-textarea v-model="formProduct.description" :label="$t('products.description')"></v-textarea>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <ImageUpload @update:image="handleImage" />
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col>
                            <v-btn color="primary" text @click="submit">{{ $t('products.addProduct') }}</v-btn>
                        </v-col>
                    </v-row>

                    
                    
                </v-container>
            </v-form>
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { ProductPayload } from '~/interface/product';

const productStore = useProductStore()
const companyStore = useCompanyStore()
const router = useRouter()
const route = useRoute()
const form = ref(null)
const formProduct:ProductPayload = ref({
    id: '',
    name: '',
    description: '',
    productImage: '',
    companies: []
})

const companyId = route.query?.companyId

const valid = ref(false)
const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
];

definePageMeta({
    title: 'Add product',
    name: 'add-product',
    layout: 'dashboard',
    middleware: 'auth'
});

const handleImage = (imageData) => {
    formProduct.value.productImage = imageData
};

const submit = () => {
    const formValidation: any = form.value
    formValidation.validate()

    if (!valid.value) {
        return
    }
    formProduct.value.id = Math.random().toString(16).substr(2, 8)

    if(companyId) {
        const company = companyStore.getCompanyById(companyId)
        formProduct.value.companies.push(company)
    }

    productStore.addProduct(formProduct.value)
    router.push('/products')
}

</script>

<style lang="scss" scoped>
.add-product {
    width: 50%;
    margin-right: auto;
    margin-left: 50px;
}
</style>
