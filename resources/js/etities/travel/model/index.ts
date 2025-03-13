import { defineStore } from "pinia"
import { travelData } from "../../../app/types/types";

export const useTravelStore = defineStore('travel',  {
    state: () => ({
        travels: [],
    }),
    actions: {
        createTravel(travel: travelData) {

        },
        updateTravel() {

        },
        deleteTravel() {

        },
    }
})
