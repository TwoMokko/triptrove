export type travelData = {
    id: number,
    place: string,
    when: string,
    amount: string,
    mode_of_transport: string,
    accommodation: string,
    advice: string,
    entertainment: string,
    general_impression: string,
    order: string,
    published: string,
    user_id: string,
    created_at: string,
    updated_at: string,
    cover: string,
    users: userShort[]
}

export type userData = {
    created_at: string,
    email: string,
    email_verified_at: string | null,
    id: number,
    name: string,
    avatar: string,
    updated_at: string,
}

interface AttachResponse {
    message: string,
    attached_count: number,
    already_attached?: number[]
}


export interface userShort {
    id: number,
    name: string,
    login: string,
    avatar: string,
    pivot: {
        travel_id: number,
        user_id: number,
        created_at: string,
        updated_at: string,
    }
}

export type travelsResponse = travelData[]

export interface OrderUpdatePayload {
    id: string | number,
    order: number,
}

