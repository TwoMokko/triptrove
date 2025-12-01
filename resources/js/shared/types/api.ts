// types/api.ts
export interface ApiResponse<T = any> {
    success: boolean;
    data?: T;
    message?: string;
    errors?: ValidationError[];
}

export interface ValidationError {
    field: string;
    message: string;
}

export interface Travel {
    id: number;
    place: string;
    when: string;
    amount: string;
    mode_of_transport: string;
    accommodation: string;
    advice: string;
    entertainment: string;
    general_impression: string;
    order: number;
    published: boolean;
    user_id: number;
    created_at: string;
    updated_at: string;
    users?: User[];
    creator?: User;
}

export interface User {
    id: number;
    name: string;
    login: string;
    avatar?: string;
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