export const textFormattingShortcuts: ({
    keyCombination: {
        modifier: string;
        character: string;
    };
    description: string;
    aliases?: undefined;
} | {
    keyCombination: {
        character: string;
        modifier?: undefined;
    };
    description: string;
    aliases?: undefined;
} | {
    keyCombination: {
        modifier: string;
        character: string;
    };
    aliases: {
        modifier: string;
        character: string;
    }[];
    description: string;
})[];
//# sourceMappingURL=config.d.ts.map