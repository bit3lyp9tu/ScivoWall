import os
import sys
import datetime
import time

import unittest
import mypy
import re
from selenium import webdriver

from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains

# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options

# from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
from selenium.webdriver.firefox.service import Service as FirefoxService


class PythonOrgSearch(unittest.TestCase):

    address = ""

    def setUp(self):
        options = Options()

        if os.environ.get("GITHUB_ACTIONS"):
            options.add_argument("--headless")

            options.binary_location = "/usr/bin/firefox"
            service = FirefoxService(
                executable_path="/home/runner/cache/.driver/geckodriver"
            )
            self.driver = webdriver.Firefox(service=service, options=options)
            self.address = "127.0.0.1:8080"
        else:
            self.driver = webdriver.Firefox(options=options)
            self.address = "localhost"

    def test_search_in_python_org(self):
        driver = self.driver
        driver.get(f"http://{self.address}/scientific_poster_generator/login.php")
        self.assertIn("Poster Generator", driver.title)
        print("Testing Page: " + driver.title)

        self.login_page(driver)

        self.user_page(driver)
        self.poster_page(driver, 3)
        self.admin_user(driver, "")
        self.index_page(driver)

        # self.logout(driver)

    def tearDown(self):
        if os.environ.get("GITHUB_ACTIONS"):
            self.driver.close()

    def login_page(self, driver):

        # check both empty
        self.login_fill_form(driver, "", "")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check wrong name
        self.login_fill_form(driver, "Max Mustermann" + "123", "AbC123-98xy")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check wrong pw
        self.login_fill_form(driver, "Max Mustermann", "AbC123-98xy" + "abc")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check right name+pw
        self.login_fill_form(driver, "max5", "abc")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        # check cookie session
        session_id = driver.get_cookie("sessionID")
        self.assertIsNotNone(session_id)

        pass

    def login_fill_form(self, driver, name, pw):
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        filed_name = driver.find_element(By.ID, "name")
        filed_name.send_keys(name)
        filed_name.send_keys(Keys.RETURN)

        filed_pw = driver.find_element(By.ID, "pw")
        filed_pw.send_keys(pw)
        filed_pw.send_keys(Keys.RETURN)

    def login_clear_form(self, driver):
        driver.find_element(By.ID, "name").clear()
        driver.find_element(By.ID, "pw").clear()

    def logout(self, driver):
        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        # check if page contains logout
        logout = driver.find_element(By.ID, "logout")
        self.assertIsNotNone(logout)
        logout.click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )

        # check if correct logout   ???
        pass

    def register(self, driver, name, pw):
        # check existing name
        # check invalid pw
        # check two diffent pw
        # check correct action + now at login page
        pass

    def user_page(self, driver):
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        # check poster list correctly loaded
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-3"
        )
        self.assertTrue(
            poster_list_element.text
            in ["2025-04-16 13:43:02 Edit", "2025-04-16 11:43:02 Edit"]
        )

        # check add new poster
        create_poster = driver.find_element(By.ID, "project-name")
        create_poster.send_keys("Test Title")
        create_poster.send_keys(Keys.RETURN)

        driver.find_element(By.CSS_SELECTOR, "#create-project>button").click()

        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr#table-container--nr-4"
        )
        self.assertIsNotNone(poster_list_element)

        # check right date
        date = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        self.assertTrue(
            self.date_compair_day(date, poster_list_element.text.split(" ")[0])
        )

        # check edit poster title
        custom_poster = driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        )
        self.assertEqual("Test Title", custom_poster.get_attribute("value"))

        custom_poster.click()
        custom_poster.send_keys(" abc")

        time.sleep(3)
        custom_poster.send_keys(Keys.TAB)
        time.sleep(3)

        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-1>td:first-child>input",
        ).click()
        time.sleep(3)

        # custom_poster2 = driver.find_element(
        #     By.CSS_SELECTOR,
        #     "#table-container>table>tr#table-container--nr-4>td:first-child>input",
        # )
        # self.assertEqual("Test Title abc", custom_poster2.get_attribute("value"))

        # check after page reload

        # check delete new poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-4>td:last-child>td>input",
        ).click()
        poster_list_element = driver.find_element(
            By.CSS_SELECTOR, "#table-container>table>tr:last-child>td:first-child>input"
        )
        self.assertTrue(
            poster_list_element not in ["Test Title", "Test Title abc"]
        )  # TODO: change to only expecting 'Test Title abc'

        # check access poster
        driver.find_element(
            By.CSS_SELECTOR,
            "#table-container>table>tr#table-container--nr-3>td:nth-last-child(2)>td>a",
        ).click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/poster.php",
            driver.current_url.split("?")[0],
        )

        driver.get(
            f"http://{self.address}/scientific_poster_generator/projects.php",
        )

        time.sleep(1)

        # check author list correctly loaded
        author_list_element = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        self.assertEqual("Lina Chen", author_list_element.get_attribute("value"))

        author_list_element2 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:nth-child(2)",
        )
        self.assertEqual("The Future of Urban Farming", author_list_element2.text)

        # check edit author name
        author_list_element3 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        author_list_element3.click()
        author_list_element3.send_keys(" abc")

        time.sleep(1)
        author_list_element3.send_keys(Keys.TAB)
        time.sleep(1)

        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-1>td:first-child>input",
        ).click()
        time.sleep(1)
        author_list_element4 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:first-child>input",
        )
        self.assertEqual("Lina Chen abc", author_list_element4.get_attribute("value"))
        self.assertTrue(
            author_list_element4.get_attribute("value")
            in ["Lina Chen", "Lina Chen abc"]
        )  # TODO: change to only expecting 'Lina Chen abc'

        # check delete author
        driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr#author-list--nr-9>td:last-child>td>input",
        ).click()
        author_list_element5 = driver.find_element(
            By.CSS_SELECTOR,
            "#author-list>table>tr:last-child>td:first-child>input",
        )
        self.assertIsNot("Alice johnson", author_list_element5.get_attribute("value"))

        # check image list  ???
        pass

    def date_compair_day(self, date1, date2):
        g1 = date1.split(" ")
        g2 = date2.split(" ")

        return g1[0] == g2[0]
        # and g1[1].split(":")[0] == g2[1].split(":")[0]

    def poster_page(self, driver, local_index):

        driver.get(
            f"http://{self.address}/scientific_poster_generator/projects.php",
        )

        time.sleep(3)

        driver.find_element(
            By.CSS_SELECTOR,
            f"#table-container>table>tr#table-container--nr-{local_index}>td:nth-last-child(2)>td>a",
        ).click()

        # check right title
        title = driver.find_element(By.CSS_SELECTOR, "div#title")
        print(title.text)
        self.assertEqual("The Future of Urban Farming", title.text)

        time.sleep(1)

        # check edit title # TODO
        # driver.find_element(By.CSS_SELECTOR, "div#titles>div>div#title").click()
        # WebDriverWait(driver, 5).until(
        #     EC.element_to_be_clickable((By.CSS_SELECTOR, "div#titles>div>div#title"))
        # ).click()
        # title2 = driver.find_element(By.CSS_SELECTOR, "textearea#title").get_attribute(
        #     "data-content"
        # )
        # title2.click()
        # title2.send_keys(" abc")
        # title2.send_keys(Keys.RETURN)
        # title3 = driver.find_element(By.CSS_SELECTOR, "div#title>p")
        # self.assertEqual("The Future of Urban Farming", title3.text)
        # #   +globally

        # check authors
        authors = set(
            [
                i.text
                for i in driver.find_elements(
                    By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
                )
            ]
        )
        self.assertEqual(authors, {"ChatGPT", "Alice Johnson"})

        # check empty authors

        # check add author  # TODO
        # author = driver.find_element(By.CSS_SELECTOR, "div#typeahead-container")
        # author.click()
        # author.send_keys("Author")
        # driver.find_element(By.ID, "logo_headline").click()
        # self.assertEqual(
        #     set(
        #         [
        #             i.text
        #             for i in driver.find_elements(
        #                 By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
        #             )
        #         ]
        #     ),
        #     {"ChatGPT", "Alice Johnson", "Author"},
        # )

        # check author list switch order
        drag = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#typeahead-container>div:nth-child(1)",
                )
            )
        )
        drop = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable(
                (
                    By.CSS_SELECTOR,
                    "div#typeahead-container>div:nth-child(2)",
                )
            )
        )
        ActionChains(driver).drag_and_drop(drag, drop).perform()
        author_order = [
            i.text
            for i in driver.find_elements(
                By.CSS_SELECTOR, "div#typeahead-container>div.author-item"
            )
        ]
        print(author_order)
        # self.assertEqual([], author_order)

        # check author stored
        # check add box
        #   +globally
        # check basic edit box
        # check box markdown render
        # check box math render

        # check box plotly render   ???
        # check box image render    ???
        #   + stored globally

        # check visibility
        #   - private
        #   - public

        # check right edit date
        pass

    def admin_user(self, driver, pw):
        # # go to projects page
        # driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        # time.sleep(1)

        # # logout
        # driver.find_element(By.ID, "logout").click()
        # time.sleep(1)

        # # login as admin
        # self.login_fill_form(driver, "Admin", "PwScaDS-2025")
        # driver.find_element(By.ID, "login").click()
        # self.assertEqual(
        #     f"http://{self.address}/scientific_poster_generator/projects.php",
        #     driver.current_url,
        # )
        # time.sleep(1)

        # check login successfully
        # check posters set on public
        # check set poster to visible
        pass

    def index_page(self, driver):
        # check poster count
        # check poster#1 content
        # check prevent editing:
        #   -title
        #   -add author
        #   -author item interaction
        #   -change author order
        #   -box text
        #   -box image drop
        #   -box interaction
        pass


if __name__ == "__main__":
    unittest.main()

    # TODO: test todo-to-issue test
    #  labels: enhancement, help wanted
