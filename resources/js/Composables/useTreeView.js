import { ref, computed } from 'vue'

export function useTreeView(items, viewMode) {

    const expandedRows = ref([])

    const toggleRow = (id) => {

        if (expandedRows.value.includes(id)) {

            expandedRows.value = expandedRows.value.filter(
                rowId => rowId !== id
            )

        } else {

            expandedRows.value.push(id)

        }

    }

    const isExpanded = (id) => {

        return expandedRows.value.includes(id)

    }

    const expandAll = () => {

        expandedRows.value = items.value
            .filter(item => item.is_header)
            .map(item => item.id)

    }

    const collapseAll = () => {

        expandedRows.value = []

    }

    const visibleItems = computed(() => {

        if (viewMode.value !== 'tree') {
            return items.value
        }

        return items.value.filter(item => {

            if (!item.parent_id) {
                return true
            }

            let parent = items.value.find(
                account => account.id === item.parent_id
            )

            while (parent) {

                if (!expandedRows.value.includes(parent.id)) {
                    return false
                }

                parent = items.value.find(
                    account => account.id === parent.parent_id
                )

            }

            return true

        })

    })

    return {

        expandedRows,

        visibleItems,

        toggleRow,

        isExpanded,

        expandAll,

        collapseAll,

    }

}