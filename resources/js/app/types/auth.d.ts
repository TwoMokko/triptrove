export interface AuthResponse {
    token: string
    user?: User
    expiresIn?: number
}

export interface User {
    id: number,
    email: string,
    login: string,
    name: string,
    email_verified_at: string | null,
    created_at: string,
    updated_at: string
    role?: string
}
