const summaryCards = computed(() => [
    {
        key: 'outstanding',
        label: 'Outstanding',
        value: totals.value.total,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-gray-500',
    },
    {
        key: 'current',
        label: 'Current',
        value: totals.value.current,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-emerald-500',
    },
    {
        key: 'days_1_30',
        label: '1–30 Days',
        value: totals.value.days_1_30,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-blue-500',
    },
    {
        key: 'days_31_60',
        label: '31–60 Days',
        value: totals.value.days_31_60,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-yellow-500',
    },
    {
        key: 'days_61_90',
        label: '61–90 Days',
        value: totals.value.days_61_90,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-orange-500',
    },
    {
        key: 'over_90',
        label: '>90 Days',
        value: totals.value.over_90,
        classes: 'bg-gray-50 border-gray-200 text-gray-900',
        accent: 'bg-red-500',
    },
])