"""Pruebas de aislamiento y disponibilidad temporal; fixtures sintéticos solo para tests."""
import tempfile
import unittest
from pathlib import Path

import numpy as np
import pandas as pd

from data_pipeline import read_dataset, build_examples, make_inputs, fit_scaler, transform, invert
from prepare_dataset import write_csv, meta_rows


def fixture(account='A', platform='facebook'):
    return [dict(platform=platform, account_id=account, post_id=f'{account}-{i}',
                 published_at=f'2026-01-{i+1:02}T12:00:00-04:00', likes=i+1, comments=2,
                 metrics_observed_at=f'2026-01-{i+1:02}T18:00:00-04:00',
                 source='meta_export', measurement_protocol='fixed_6h') for i in range(10)]


class DataTests(unittest.TestCase):
    def load(self, rows):
        with tempfile.TemporaryDirectory() as tmp:
            path = Path(tmp) / 'input.csv'
            write_csv(path, rows)
            return read_dataset(path)

    def test_no_cross_account_or_network_sequences(self):
        a = self.load(fixture())
        mixed = self.load(fixture() + fixture('B') + fixture('C', 'instagram'))
        h, c, y, r = build_examples(mixed, 3)
        expected = build_examples(a, 3)
        np.testing.assert_allclose(h[r.account_id.eq('A')], expected[0])
        np.testing.assert_allclose(c[r.account_id.eq('A')], expected[1])
        self.assertEqual(len(r), 3*len(expected[3]))
        with self.assertRaises(ValueError):
            make_inputs(mixed, pd.Timestamp('2026-03-01T12:00:00-04:00'), 3)

    def test_future_metrics_cannot_change_earlier_input(self):
        original = self.load(fixture())
        altered = original.copy()
        altered.loc[altered.index[-2:], ['likes', 'comments', 'score', 'score_log']] = 99999
        original_inputs = build_examples(original, 3)
        altered_inputs = build_examples(altered, 3)
        early = original_inputs[3].published_at < original.published_at.iloc[-2]
        np.testing.assert_array_equal(original_inputs[0][early], altered_inputs[0][early])
        np.testing.assert_array_equal(original_inputs[1][early], altered_inputs[1][early])

    def test_snapshot_cannot_reconstruct_past_availability(self):
        rows = fixture()
        for row in rows:
            row['metrics_observed_at'] = '2026-02-01T00:00:00-04:00'
        self.assertTrue(build_examples(self.load(rows), 3)[3].empty)

    def test_prior_uses_only_eligible_history(self):
        frame = self.load(fixture())
        timestamp = frame.published_at.iloc[5]
        expected = make_inputs(frame.iloc[:5], timestamp, 3)
        actual = make_inputs(frame, timestamp, 3)
        np.testing.assert_allclose(actual[0], expected[0])
        np.testing.assert_allclose(actual[1], expected[1])

    def test_missing_metric_duplicates_and_naive_dates_rejected(self):
        for mutation in ('missing', 'duplicate', 'naive', 'negative', 'observation'):
            rows = fixture()
            if mutation == 'missing': rows[0]['likes'] = ''
            if mutation == 'duplicate': rows.append(rows[0].copy())
            if mutation == 'naive': rows[0]['published_at'] = '2026-01-01T12:00:00'
            if mutation == 'negative': rows[0]['comments'] = -1
            if mutation == 'observation': rows[0]['metrics_observed_at'] = ''
            with self.subTest(mutation=mutation), self.assertRaises(ValueError):
                self.load(rows)

    def test_scaler_round_trip(self):
        x = np.array([[1, 2, 5], [1, 4, 7]], dtype='float32')
        scaler = fit_scaler(x)
        np.testing.assert_allclose(invert(transform(x, scaler), scaler), x)


if __name__ == '__main__':
    unittest.main()
