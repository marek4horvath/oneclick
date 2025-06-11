<template>
    <NuxtLayout>
        <div class="add-company">
            <v-form ref="form" v-model="valid" >
                <v-container>
                    <v-row>
                        <v-col cols="6" class="pb-0">
                            <v-text-field 
                                v-model="formCompany.name" 
                                :label="$t('companies.companyName')"
                                type="text" 
                                required
                                :rules="nameRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6" class="pb-0">
                            <v-text-field 
                                v-model="formCompany.street" 
                                :label="$t('address.street')" 
                                type="text" 
                                required
                                :rules="streetRules"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="formCompany.zip" 
                                :label="$t('address.zip')" 
                                type="text" 
                                required
                                :rules="zipRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="formCompany.houseNo" 
                                :label="$t('address.houseNumber')" 
                                type="text" 
                                required
                                :rules="houseNumberRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="formCompany.city" 
                                :label="$t('address.city')" 
                                type="text" 
                                required
                                :rules="cityRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <v-select
                                :label="$t('address.country')"
                                v-model="formCompany.country" 
                                :items="europeanCountries"
                                required
                                :rules="countryRules"
                            ></v-select>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="formCompany.contact"
                                :label="$t('companies.contact')" 
                                type="text" 
                                required
                                :rules="contactRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="formCompany.email"
                                :label="$t('companies.email')" 
                                type="text" 
                                required
                                :rules="emailRules"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="12">
                            <ImageUpload @update:image="handleImage" />
                        </v-col>
                        
                        <v-col cols="12">
                            <v-btn color="primary" text @click="submit">{{ $t('companies.addCompany') }}</v-btn>
                        </v-col>
                    </v-row>

                </v-container>
            </v-form>
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { CompanyPayload } from '~/interface/company';
import { EUROPEANCOUNTRIES } from '~/types/countries';

const europeanCountries = ref(EUROPEANCOUNTRIES)
const companyStore = useCompanyStore()
const router = useRouter()
const form = ref(null)
const formCompany:CompanyPayload = ref({
    id: '',
    name: '',
    companyLogo: '',
    street: '',
    zip: '',
    houseNo: '',
    city: '',
    country: '',
    contact: '',
    email: '',
})
const valid = ref(false)

const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
];

const streetRules = [
    (v: string) => !!v || 'Street is required',
    (v: string) => v.trim().length > 0 || 'Street cannot be empty',
];

const zipRules = [
    (v: string) => !!v || 'Zip is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
];

const houseNumberRules = [
    (v: string) => !!v || 'House number is required',
    (v: string) => v.trim().length > 0 || 'House number cannot be empty',
];

const cityRules = [
    (v: string) => !!v || 'City is required',
    (v: string) => v.trim().length > 0 || 'Zip cannot be empty',
];

const countryRules = [
    (v: string) => !!v || 'Country is required',
    (v: string) => v.trim().length > 0 || 'Country cannot be empty',
];

const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
];

const contactRules = [
    (v: string) => !!v || 'Phone number is required',
    (v: string) => /^\d+$/.test(v) || 'Phone number must contain only numbers',
    (v: string) => v.length >= 8 || 'Phone number must be at least 8 digits long',
    (v: string) => v.length <= 15 || 'Phone number must be at most 15 digits long',
];


definePageMeta({
    title: 'Add company',
    name: 'add-company',
    layout: 'dashboard',
    middleware: 'auth'
});

const handleImage = (imageData) => {
    formCompany.value.companyLogo = imageData
};

const submit = () => {
    const formValidation: any = form.value
    formValidation.validate()

    if (!valid.value) {
        return
    }
    formCompany.value.id = Math.random().toString(16).substr(2, 8)

    companyStore.addCompanies(formCompany.value)
    router.push('/companies')
}


</script>

<style lang="scss" scoped>
.add-company {
    width: 50%;
    margin-right: auto;
    margin-left: 50px;

}
</style>