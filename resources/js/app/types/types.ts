export type travelData = {
    id: number,
    place: string,
    date: string,
    mode_of_transport: string,
    good_impression: string,
    bad_impression: string,
    general_impression: string,
    user_id: number,
    order: number,
    timestamps?: string,
    lock?: boolean,
}

export type userData = {
    created_at: string,
    email: string,
    email_verified_at: string | null,
    id: number,
    name: string,
    updated_at: string
}
