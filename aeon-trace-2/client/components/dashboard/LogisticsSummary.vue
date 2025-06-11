<script setup lang="ts">
import * as d3 from 'd3'
import { feature } from 'topojson-client'
import worldMap from '@/assets/map/countries-110m.json'

const svgRef = ref(null)
const width = 650
const height = 345

const highlightedRegions = [
    'Canada',
    'United States of America',
    'Mexico',
    'Slovakia',
    'Czechia',
    'Greece',
    'Portugal',
    'Spain',
    'Japan',
    'China',
    'Qatar',
    'Brazil',
]

const drawMap = svgElement => {
    const svg = d3.select(svgElement)

    svg.selectAll('*').remove()

    const projection = d3
        .geoNaturalEarth1()

    const path = d3.geoPath().projection(projection)

    const geoJSONData = feature(worldMap, worldMap.objects.countries).features.filter(
        d => d.properties.name !== 'Antarctica',
    )

    projection.fitSize([width, height], {
        type: 'FeatureCollection',
        features: geoJSONData,
    })

    // Draw the map
    svg
        .append('g')
        .selectAll('path')
        .data(geoJSONData)
        .join('path')
        .attr('d', path)
        .attr('fill', d =>
            highlightedRegions.includes(d.properties.name)
                ? '#26A69A'
                : '#D8DADB',
        )
        .attr('stroke', '#ffffff')
}

onMounted(() => {
    drawMap(svgRef.value)
})
</script>

<template>
    <div class="logistics-summary">
        <VRow>
            <VCol
                cols="auto"
                class="logistics-status-col"
            >
                <VCard
                    class="logistics-status"
                    variant="flat"
                >
                    <span class="title">{{ $t('dashboard.logisticsStatus') }}</span>
                    <div class="icon-background">
                        <PhosphorIconTruckTrailer
                            size="61"
                            class="card-icon"
                        />
                    </div>
                    <p class="status">
                        {{ $t('dashboard.flagStatus') }}
                    </p>
                </VCard>
            </VCol>
            <VCol>
                <VCard
                    class="logistics-distribution"
                    variant="flat"
                >
                    <div class="incoming">
                        <span class="title">{{ $t('dashboard.incoming') }}</span>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Expected products & material</span>
                                <span class="value">32 kg</span>
                            </div>
                        </div>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Expect delivery time</span>
                                <span class="value">in 02:15:34</span>
                            </div>
                        </div>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Delayed product</span>
                                <span class="value">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="map">
                        <svg
                            ref="svgRef"
                            :width="width"
                            :height="height"
                        />
                    </div>
                    <div class="outgoing">
                        <span class="title">Outgoing</span>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Deliveries</span>
                                <span class="value">266</span>
                            </div>
                        </div>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Expected delivery time</span>
                                <span class="value">in 24:50:04</span>
                            </div>
                        </div>
                        <div class="incoming-stats">
                            <div class="icon-background">
                                <PhosphorIconCurrencyDollarSimple
                                    size="33"
                                    class="card-icon"
                                />
                            </div>
                            <div class="stats-content">
                                <span class="term">Of stock products</span>
                                <span class="value">1228</span>
                            </div>
                        </div>
                    </div>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>
