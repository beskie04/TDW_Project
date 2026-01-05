<?php
/**
 * Table Component (Generic Framework Component)
 * Data table with headers and rows
 * 
 * @param array $options Configuration:
 *   - 'headers' (array): Column headers ['Column 1', 'Column 2', ...]
 *   - 'rows' (array): Data rows, each row is array of cells
 *   - 'striped' (bool): Striped rows (default: true)
 *   - 'hoverable' (bool): Hover effect (default: true)
 *   - 'bordered' (bool): Border style (default: false)
 *   - 'responsive' (bool): Responsive wrapper (default: true)
 *   - 'cssClass' (string): Additional CSS classes
 * @param callable $rowRenderer Optional callback to render custom rows: function($row, $index)
 */

class Table
{
    public static function render($options = [], $rowRenderer = null)
    {
        $defaults = [
            'headers' => [],
            'rows' => [],
            'striped' => true,
            'hoverable' => true,
            'bordered' => false,
            'responsive' => true,
            'cssClass' => ''
        ];

        $options = array_merge($defaults, $options);

        $classes = ['data-table'];
        if ($options['striped'])
            $classes[] = 'table-striped';
        if ($options['hoverable'])
            $classes[] = 'table-hover';
        if ($options['bordered'])
            $classes[] = 'table-bordered';
        if ($options['cssClass'])
            $classes[] = $options['cssClass'];

        $classString = implode(' ', $classes);
        ?>
        <?php if ($options['responsive']): ?>
            <div class="table-container">
            <?php endif; ?>

            <table class="<?= $classString ?>">
                <?php if (!empty($options['headers'])): ?>
                    <thead>
                        <tr>
                            <?php foreach ($options['headers'] as $header): ?>
                                <th><?= htmlspecialchars($header) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                <?php endif; ?>

                <tbody>
                    <?php if ($rowRenderer && is_callable($rowRenderer)): ?>
                        <?php foreach ($options['rows'] as $index => $row): ?>
                            <?php $rowRenderer($row, $index); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($options['rows'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= htmlspecialchars($cell) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($options['responsive']): ?>
            </div>
        <?php endif; ?>
    <?php
    }
}
?>