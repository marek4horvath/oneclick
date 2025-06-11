<template>
    <v-container class="battery-pass">
        <div>
            <p>{{ $t('dppDetail.title') }}</p>
        </div>

        <v-row style="background: rgb(var(--v-theme-primary));" class="mt-4 mb-4">
                <v-col cols="12" md="10">
                    <v-row>
                        <v-col cols="12" md="3">
                            <v-card outlined class="pa-3">
                                <v-card-title class="title">
                                    <h5>{{ $t('dppDetail.cardTitle') }}</h5>
                                </v-card-title>
                                <v-card-text>
                                    <p>{{ $t('dppDetail.batteryId') }}: 535363</p>
                                    <p>{{ $t('dppDetail.batteryPassport') }}: 6545645645</p>
                                    <p>{{ $t('dppDetail.responsibleEconomic') }}</p>
                                    <br />
                                    <h4>{{ $t('dppDetail.infoPanel.headers.header1') }}</h4>
                                    <v-divider class="my-2"></v-divider>

                                    <div class="info-panel">
                                        <div class="content">
                                            <PhFingerprintSimple :size="40" />
                                            <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content1') }}</p>
                                        </div>
                                        <div class="content">
                                            <PhCar :size="20" />
                                            <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content2') }}</p>
                                        </div>

                                        <div class="content">
                                            <PhDatabase :size="20" />
                                            <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content3') }}</p>
                                        </div>

                                        <div class="content">
                                            <PhRecycle :size="20" />
                                            <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content4') }}</p>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>
                        <v-col cols="12" md="9">
                            <v-card outlined class="pa-3">
                                    
                                <v-row>
                                    <v-col cols="12" md="4">
                                        <h5>{{ $t('dppDetail.infoPanel.headers.header2') }} <sup>1</sup></h5>
                                        <v-divider class="my-2"></v-divider>

                                        <div class="info-panel">
                                            <div class="content">
                                                <PhTag :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content5') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhQuestion :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content6') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhFileText :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content7') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhCheckCircle :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content8') }}</p>
                                            </div>
                                        </div>

                                        <h5>{{ $t('dppDetail.infoPanel.headers.header6') }}</h5>
                                        <v-divider class="my-2"></v-divider>
                                        
                                        <div class="info-panel">
                                            <div class="content">
                                                <PhThermometerSimple :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content9') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhCursor :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content10') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhTrafficCone :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content11') }}</p>
                                            </div>

                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="4">
                                        <h5>{{ $t('dppDetail.infoPanel.headers.header3') }}</h5>
                                        <v-divider class="my-2"></v-divider>
                                        <div class="info-panel">
                                            <div class="content">  
                                                <PhFileText :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content12') }}</p>
                                            </div>
                                        </div>

                                        <h5>{{ $t('dppDetail.infoPanel.headers.header5') }}</h5>
                                        <v-divider class="my-2"></v-divider>

                                        <div class="info-panel">
                                            <div class="content">
                                                <PhWarning :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content13') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhGraph :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content14') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhCar :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content15') }}</p>
                                            </div>

                                            <div class="content">
                                                <PhBatteryPlus :size="32" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content16') }}</p>
                                            </div>
                                            
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="4">
                                        <h5>{{ $t('dppDetail.infoPanel.headers.header4') }}</h5>
                                        <v-divider class="my-2"></v-divider>

                                        <div class="info-panel">
                                            <div class="content">  
                                                <PhRecycle :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content17') }}</p>
                                            </div>

                                            <div class="content">  
                                                <PhScrewdriver :size="32" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content18') }}</p>
                                            </div>

                                            <div class="content">  
                                                <PhGear :size="32" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content19') }}</p>
                                            </div>

                                            <div class="content">  
                                                <PhHardHat :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content20') }}</p>
                                            </div>
                                        </div>

                                        <h5>{{ $t('dppDetail.infoPanel.headers.header7') }}</h5>
                                        <v-divider class="my-2"></v-divider>

                                        <div class="info-panel">
                                            <div class="content">
                                                <PhPulse :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content21') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhBatteryCharging :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content22') }}</p>
                                            </div>
                                            <div class="content">
                                                <PhCar :size="20" />
                                                <p class="ml-2">{{ $t('dppDetail.infoPanel.contents.content23') }}</p>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-col>
                <v-col class="banner" cols="12" md="2">
                </v-col>
        </v-row>

        <div class="legend">
            <div class="header mb-4">
                <h3>{{ $t('dppDetail.legend.title') }}</h3>
                <v-row class="mt-3">
                    <v-col cols="12" md="6">
                        <h3>{{ $t('dppDetail.legend.groups.title') }} </h3>
                        <div class="groups">
                            <div class="group">
                                <div class="marked light-gray" ></div>
                                <div class="title">{{ $t('dppDetail.legend.groups.mark1') }}</div>
                            </div>

                            <div class="group">
                                <div class="marked light-green" ></div>
                                <div class="title">{{ $t('dppDetail.legend.groups.mark2') }}</div>
                            </div>

                            <div class="group">
                                <div class="marked standard-green" ></div>
                                <div class="title">{{ $t('dppDetail.legend.groups.mark3') }}</div>
                            </div>

                            <div class="group">
                                <div class="marked dark-green" ></div>
                                <div class="title">{{ $t('dppDetail.legend.groups.mark4') }}</div>
                            </div>
                        </div>
                    </v-col>

                    <v-col cols="12" md="6">
                        <h3>{{ $t('dppDetail.legend.categories.title') }} </h3>
                        <div class="groups">
                            <div class="group">
                                <PhBatteryCharging :size="20" />
                                <div class="title ml-3">{{ $t('dppDetail.legend.categories.category1') }}</div>
                            </div>

                            <div class="group ml-5">
                                <PhCar :size="20" />
                                <div class="title ml-3">{{ $t('dppDetail.legend.categories.category2') }}</div>
                            </div>

                            <div class="group ml-5">
                                <PhPersonSimpleBike :size="20" />
                                <div class="title ml-3">{{ $t('dppDetail.legend.categories.category3') }}</div>
                            </div>

                            <div class="group ml-5">
                                <PhWindmill :size="20" />
                                <div class="title ml-3">{{ $t('dppDetail.legend.categories.category4') }} <sup>2</sup> > 2 kWh</div>
                            </div>

                            <div class="group ml-10">
                                <PhNuclearPlant :size="20" />
                                <div class="title ml-3">{{ $t('dppDetail.legend.categories.category5') }}</div>
                            </div>
                        </div>
                    </v-col>
                </v-row>

            </div>

            <v-row>
                <table cellpadding="10" cellspacing="5">
                    <thead>
                        <tr>
                            <th>{{ $t('dppDetail.legend.table.headers.header1') }}</th>
                            <th style="width: 180px;">{{ $t('dppDetail.legend.table.headers.header2') }} <br/> ({{ $t('dppDetail.legend.table.headers.header3') }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">  
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader1') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>
                                    
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content1') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content2') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content3') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content4') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content5') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content6') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content7') }}</div>
                                            </div>
                                            <div class="group light-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content8') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>
                            </td>
                            <td>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader2') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>
                                    

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content9') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content10') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content11') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content12') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content13') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content14') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content15') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>
                            </td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader3') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content16') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content17') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content18') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content19') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content20') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>
                            </td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader4') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>
                                    
                                    <v-col cols="12" md="12">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content21') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader5') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content22') }}</div>
                                            </div>
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content23') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content24') }}</div>
                                            </div>
            
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content25') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content26') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader6') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content27') }}</div>
                                            </div>
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content28') }}</div>
                                            </div>
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content29') }}</div>
                                            </div>
            
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content30') }}</div>
                                            </div>
                                            <div class="group dark-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content31') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content32') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic1') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content33') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content34') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content35') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic2') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content36') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content37') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="40" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content38') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic3') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">  
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader7') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>
                                    
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content39') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content40') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="28" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content41') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content42') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content43') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhCar :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content44') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhCar :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content45') }}</div>
                                            </div>

                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content46') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                </v-row>
                            </td>
                            <td>
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic4') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content47') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content48') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="23" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content49') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content50') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content51') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic5') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content52') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content53') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content54') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content55') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content56') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content57') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic6') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content58') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content59') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content60') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic7') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">  
                                    <div class="groups w-100">
                                        <div class="group">
                                            <p>{{ $t('dppDetail.legend.table.subHeaders.subHeader8') }}</p>
                                        </div>
                                        <div class="line"></div>
                                    </div>
                                    
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="40" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content61') }}
                                                </div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content62') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="25" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content63') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhCar :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content64') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhCar :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content65') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content66') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content67') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content68') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content69') }}</div>
                                            </div>

                                        </div>
                                    </v-col>

                                </v-row>
                            </td>
                            <td>
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic8') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content70') }}</div>
                                            </div>
                                            <div class="group light-gray">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content71') }}</div>
                                            </div>
                                        </div>
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content72') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content73') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic9') }}</p>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <v-row class="mb-2 mt-2">
                                    <div class="groups w-100">
                                        <div class="line grey"></div>
                                    </div>
                                    <v-col cols="12" md="6">
                                    </v-col>

                                    <v-col cols="12" md="6">
                                        <div class="groups">
                                            <div class="group standard-green">
                                                <div class="group-icon">
                                                    <PhPersonSimpleBike :size="20" />
                                                    <PhNuclearPlant :size="20" />
                                                </div>
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content74') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content75') }}</div>
                                            </div>
                                            <div class="group standard-green">
                                                <PhBatteryCharging :size="20" />
                                                <div class="title ml-3">{{ $t('dppDetail.legend.table.contents.content76') }}</div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </td>
                            <td class="pa-4">
                                <p>{{ $t('dppDetail.legend.table.subTopic.topic10') }}</p>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </v-row>
        </div>
    </v-container>
  </template>

<script setup lang="ts">
import { 
    PhFingerprintSimple, 
    PhCar, 
    PhDatabase, 
    PhRecycle, 
    PhTag, 
    PhQuestion, 
    PhFileText, 
    PhCheckCircle, 
    PhThermometerSimple, 
    PhCursor, 
    PhTrafficCone,
    PhBatteryCharging,
    PhPulse,
    PhHardHat,
    PhGear,
    PhScrewdriver,
    PhWarning,
    PhGraph,
    PhBatteryPlus,
    PhPersonSimpleBike,
    PhWindmill,
    PhNuclearPlant
} from "@phosphor-icons/vue";
</script>
  
<style scoped lang="scss">
.info-panel {
    margin-bottom: 30px;

    .content {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        p {
            font-size: 14px;
        }
    }
}

.banner {
    background-image: url('../assets/images/dpp-detail-banner.png');
    background-size: 100%;
    background-repeat: no-repeat;
    background-position: center;
}

.legend {
    border: 1px solid #ccc;
    padding: 30px;

    .light-gray {background-color: #f2f2f2;}
    .light-green {background-color: #cdf7cb;}
    .standard-green {background-color: #96ed89;}
    .dark-green {background-color: #6cc253;}

    .groups {
        .group {
            display: flex;
            align-items: center;

            .group-icon {
                display: flex;
            }

            .marked {
                height: 15px;
                width: 30px;
                border: 1px solid;
                margin-right: 10px;
            }
        }
    }

    table {
        width: 100%;

        thead {
            tr {
                text-align: left;
            }
        }

        tbody {
            tr {
                position: relative;

                td {
                    p {
                        font-weight: 900;
                    }

                    .line {
                        border-bottom: 1px solid;
                        width: 100%;
                        position: absolute;

                        &.grey {
                            border-color: #ccc;
                        }
                    }
                }
            }
        }
    }
}
</style>
